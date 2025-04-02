<?php

namespace Tests\Unit\Models;

use App\Models\Bike;
use App\Models\Borrower;
use App\Models\Car;
use App\Models\Community;
use App\Models\Loan;
use App\Models\Loanable;
use App\Models\Owner;
use App\Models\Trailer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class LoanableTest extends TestCase
{
    public $borough;
    public $community;
    public $otherCommunity;

    public $memberOfBorough;
    public $memberOfCommunity;
    public $otherMemberOfCommunity;
    public $memberOfOtherCommunity;

    public $boroughLoanable;

    public function setUp(): void
    {
        parent::setUp();

        // Linking users and communities would trigger RegistrationApprovedEvent
        // which would then send email using an external service.
        // withoutEvents() makes the test robust to a non-existent or
        // incorrectly-configured email service.
        $this->withoutEvents();

        $this->borough = factory(Community::class)->create([
            "type" => "borough",
        ]);
        $this->community = factory(Community::class)->create([
            "parent_id" => $this->borough->id,
        ]);
        $this->otherCommunity = factory(Community::class)->create([
            "parent_id" => $this->borough->id,
        ]);

        $this->memberOfBorough = factory(User::class)->create([
            "name" => "memberOfBorough",
        ]);
        $this->borough->users()->attach($this->memberOfBorough, [
            "approved_at" => new \DateTime(),
        ]);

        $this->memberOfCommunity = factory(User::class)->create([
            "name" => "memberOfCommunity",
        ]);
        $this->community->users()->attach($this->memberOfCommunity, [
            "approved_at" => new \DateTime(),
        ]);

        $this->otherMemberOfCommunity = factory(User::class)->create([
            "name" => "otherMemberOfCommunity",
        ]);
        $this->community->users()->attach($this->otherMemberOfCommunity);

        $this->memberOfOtherCommunity = factory(User::class)->create([
            "name" => "memberOfOtherCommunity",
        ]);
        $this->otherCommunity->users()->attach($this->memberOfOtherCommunity, [
            "approved_at" => new \DateTime(),
        ]);

        foreach (
            [
                $this->memberOfBorough,
                $this->memberOfCommunity,
                $this->otherMemberOfCommunity,
                $this->memberOfOtherCommunity,
            ]
            as $member
        ) {
            $member->owner = new Owner();
            $member->owner->user()->associate($member);
            $member->owner->save();
        }

        foreach (
            [
                $this->memberOfCommunity,
                $this->otherMemberOfCommunity,
                $this->memberOfOtherCommunity,
            ]
            as $member
        ) {
            factory(Trailer::class)->create([
                "name" => "$member->name trailer",
                "owner_id" => $member->owner->id,
            ]);
        }
    }

    public function testIsAvailableEventIfLoanExistsWithIntentionInProcessOrCanceled()
    {
        $bike = factory(Bike::class)->create([
            "owner_id" => $this->memberOfCommunity->owner->id,
            "community_id" => $this->community->id,
        ]);

        $user = factory(User::class)
            ->states("withBorrower")
            ->create();
        $loan = factory(Loan::class)
            ->states("withInProcessIntention")
            ->create([
                "borrower_id" => $user->borrower->id,
                "loanable_id" => $bike->id,
                "community_id" => $this->community->id,
                "departure_at" => "3000-10-10 10:10:00",
                "duration_in_minutes" => 60,
            ]);

        $canceledLoan = factory(Loan::class)
            ->states("withCompletedIntention", "withInProcessPrePayment")
            ->create([
                "borrower_id" => $user->borrower->id,
                "loanable_id" => $bike->id,
                "community_id" => $this->community->id,
                "departure_at" => "3000-10-11 10:10:00",
                "duration_in_minutes" => 60,
                "canceled_at" => now(),
            ]);

        $confirmedLoan = factory(Loan::class)
            ->states("withCompletedIntention", "withInProcessPrePayment")
            ->create([
                "borrower_id" => $user->borrower->id,
                "loanable_id" => $bike->id,
                "community_id" => $this->community->id,
                "departure_at" => "3000-10-12 10:10:00",
                "duration_in_minutes" => 60,
            ])
            ->refresh();
        $confirmedLoan->intention->complete()->save();
        $confirmedLoan->refresh();

        $this->assertEquals(
            true,
            $bike->isAvailable("3000-10-10 10:20:00", 60)
        );
        $this->assertEquals(
            true,
            $bike->isAvailable("3000-10-10 11:20:00", 60)
        );

        $this->assertEquals(
            true,
            $bike->isAvailable("3000-10-11 10:20:00", 60)
        );
        $this->assertEquals(
            true,
            $bike->isAvailable("3000-10-11 11:20:00", 60)
        );

        $this->assertEquals(
            false,
            $bike->isAvailable("3000-10-12 10:20:00", 60)
        );
        $this->assertEquals(
            true,
            $bike->isAvailable("3000-10-12 11:20:00", 60)
        );
    }

    public function testIsAvailableEarlyIfPaidBeforeDurationInMinutes()
    {
        $loan = factory(Loan::class)
            ->states("withAllStepsCompleted")
            ->create([
                "duration_in_minutes" => 60,
            ]);

        $bike = $loan->loanable;

        $this->assertEquals(
            true,
            $bike->isAvailable(Carbon::now()->add(61, "minutes"), 60)
        );
        $this->assertEquals(
            false,
            $bike->isAvailable(Carbon::now()->add(59, "minutes"), 60)
        );
        $this->assertEquals(
            false,
            $bike->isAvailable(Carbon::now()->add(31, "minutes"), 60)
        );
        $this->assertEquals(
            false,
            $bike->isAvailable(Carbon::now()->add(29, "minutes"), 60)
        );

        // The loan was completed earlier
        $payment = $loan->payment()->first();
        $payment->executed_at = Carbon::now()->add(30, "minutes");
        $payment->save();

        $this->assertEquals(
            true,
            $bike->isAvailable(Carbon::now()->add(61, "minutes"), 60)
        );
        $this->assertEquals(
            true,
            $bike->isAvailable(Carbon::now()->add(59, "minutes"), 60)
        );
        $this->assertEquals(
            true,
            $bike->isAvailable(Carbon::now()->add(31, "minutes"), 60)
        );
        $this->assertEquals(
            false,
            $bike->isAvailable(Carbon::now()->add(29, "minutes"), 60)
        );
    }

    public function testLoanableAvailabilityRulesAreEmptyWhenInvalid()
    {
        $trailer = factory(Trailer::class)->create([
            "availability_json" => "[{",
        ]);

        Log::shouldReceive("error")
            ->once()
            ->withArgs(function ($message) use ($trailer) {
                return strpos($message, "\"[{\"") !== false &&
                    strpos($message, (string) $trailer->id) !== false;
            });

        $rules = $trailer->getAvailabilityRules();

        $this->assertEquals([], $rules);
    }

    public function testLoanableAvailabilityRules()
    {
        $trailer = factory(Trailer::class)->create([
            "availability_json" =>
                '[{"available":true,"type":"weekdays","scope":["MO","TU","WE","TH","FR"],"period":"00:00-24:00"}]',
        ]);

        $rules = $trailer->getAvailabilityRules();

        $expected = [
            [
                "available" => true,
                "type" => "weekdays",
                "scope" => ["MO", "TU", "WE", "TH", "FR"],
                "period" => "00:00-24:00",
            ],
        ];

        $this->assertEquals($expected, $rules);
    }

    public function testAddCoowner()
    {
        $loanableClasses = [Trailer::class, Car::class, Bike::class];

        foreach ($loanableClasses as $loanableClass) {
            $loanable = factory($loanableClass)->create();
            $user = factory(User::class)->create();

            $loanable->addCoowner($user->id);
            $loanable->refresh();

            self::assertCount(
                1,
                $loanable->coowners,
                "$loanableClass has 1 coowner"
            );
            self::assertEquals($user->id, $loanable->coowners[0]->user->id);
            self::assertEquals(
                $loanable->id,
                $loanable->coowners[0]->loanable->id,
                "$loanableClass has the correct coowner"
            );
        }
    }

    public function testRemoveCoowner()
    {
        $loanableClasses = [Trailer::class, Car::class, Bike::class];

        foreach ($loanableClasses as $loanableClass) {
            $loanable = factory($loanableClass)->create();
            $user = factory(User::class)->create();
            $loanable->addCoowner($user->id);
            $loanable->refresh();

            $loanable->removeCoowner($user->id);

            self::assertEmpty(
                $loanable->coowners,
                "$loanableClass has no coowner"
            );
        }
    }

    public function testDefaultCoownerFlags()
    {
        $loanable = factory(Car::class)->create();
        $user = factory(User::class)->create();
        $loanable->addCoowner($user->id);
        $loanable->refresh();
        self::assertEquals(false, $loanable->coowners[0]->receive_notifications);
        self::assertEquals(true, $loanable->coowners[0]->pays_loan_price);
        self::assertEquals(true, $loanable->coowners[0]->pays_provisions);
        self::assertEquals(true, $loanable->coowners[0]->pays_owner);
    }

    public function testWhoIsPayingForLoans()
    {
        $loanable = factory(Car::class)->create();
        $user = factory(User::class)->create();
        $coowner = factory(User::class)->create();
        $non_paying_coowner = factory(User::class)->create();
        $loanable->addCoowner($coowner->id);
        $loanable->addCoowner($non_paying_coowner->id);
        $loanable->refresh();

        $loanable->coowners[1]->pays_loan_price = false;
        $loanable->coowners[1]->save();

        self::assertEquals(true, $loanable->isPayingForLoans($user));
        self::assertEquals(true, $loanable->isPayingForLoans($coowner));
        self::assertEquals(false, $loanable->isPayingForLoans($non_paying_coowner));
    }
}
