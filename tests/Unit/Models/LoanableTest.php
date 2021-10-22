<?php

namespace Tests\Unit\Models;

use App\Models\Borrower;
use App\Models\Community;
use App\Models\Bike;
use App\Models\Car;
use App\Models\Loan;
use App\Models\Loanable;
use App\Models\Owner;
use App\Models\Trailer;
use App\Models\User;
use Carbon\Carbon;
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

    public $communityLoanable; // TODO
    public $boroughLoanable; // TODO

    public function setUp(): void
    {
        parent::setUp();

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

    public function testLoanableNotAccessibleAccrossCommunitiesByDefault()
    {
        foreach (
            [
                $this->memberOfCommunity,
                $this->otherMemberOfCommunity,
                $this->memberOfOtherCommunity,
            ]
            as $member
        ) {
            $loanables = Loanable::accessibleBy($member)->pluck("name");
            $loanableIds = Loanable::accessibleBy($member)->pluck("id");
            $this->assertEquals(
                1,
                $loanables->count(),
                "too many loanables accessible to $member->name"
            );
            $this->assertEquals(
                $member->loanables()->first()->id,
                $loanableIds->first()
            );
        }
    }

    public function testLoanableBecomesAccessibleIfCommunityMembershipIsApproved()
    {
        $loanables = Loanable::accessibleBy($this->memberOfCommunity)->pluck(
            "id"
        );
        $this->assertEquals(1, $loanables->count());
        $this->assertEquals(
            $this->memberOfCommunity->loanables()->first()->id,
            $loanables->first()
        );

        $this->community
            ->users()
            ->updateExistingPivot($this->otherMemberOfCommunity->id, [
                "approved_at" => new \DateTime(),
            ]);

        $loanables = Loanable::accessibleBy($this->memberOfCommunity)
            ->pluck("id")
            ->sort();
        $this->assertEquals(2, $loanables->count());
        $this->assertEquals(
            $this->memberOfCommunity->loanables()->first()->id,
            $loanables[0]
        );
        $this->assertEquals(
            $this->otherMemberOfCommunity->loanables()->first()->id,
            $loanables[1]
        );
    }

    public function testCarBecomesAccessibleIfBorrowerIsApproved()
    {
        $car = factory(Car::class)->create([
            "owner_id" => $this->memberOfCommunity->owner->id,
        ]);

        $loanables = Loanable::accessibleBy($this->memberOfCommunity)->pluck(
            "id"
        );
        $this->assertEquals(2, $loanables->count());

        $loanables = Loanable::accessibleBy(
            $this->otherMemberOfCommunity
        )->pluck("id");
        $this->assertEquals(1, $loanables->count());
        $this->assertEquals(
            $this->otherMemberOfCommunity->loanables()->first()->id,
            $loanables[0]
        );

        $this->community
            ->users()
            ->updateExistingPivot($this->otherMemberOfCommunity->id, [
                "approved_at" => new \DateTime(),
            ]);

        $this->otherMemberOfCommunity = $this->otherMemberOfCommunity->fresh();

        $loanables = Loanable::accessibleBy($this->otherMemberOfCommunity)
            ->orderBy("id")
            ->pluck("id");
        $this->assertEquals(2, $loanables->count());
        $firstId = $this->memberOfCommunity->loanables()->first()->id;
        $this->assertEquals(
            $firstId,
            $loanables[0],
            "$firstId not in " . implode(",", $loanables->toArray())
        );
        $this->assertEquals(
            $this->otherMemberOfCommunity->loanables()->first()->id,
            $loanables[1]
        );

        $borrower = new Borrower();
        $borrower->user()->associate($this->otherMemberOfCommunity);
        $borrower->approved_at = new \DateTime();
        $borrower->save();

        $this->otherMemberOfCommunity = $this->otherMemberOfCommunity->fresh();

        $loanables = Loanable::accessibleBy($this->otherMemberOfCommunity)
            ->orderBy("id")
            ->pluck("id");
        $this->assertEquals(3, $loanables->count());
        $this->assertEquals(
            $this->memberOfCommunity->loanables()->first()->id,
            $loanables[0]
        );
        $this->assertEquals(
            $this->otherMemberOfCommunity->loanables()->first()->id,
            $loanables[1]
        );
        $this->assertEquals($car->id, $loanables[2]);
    }

    public function testLoanableAccessibleThroughInheritedClasses()
    {
        factory(Car::class)->create([
            "owner_id" => $this->memberOfCommunity->owner->id,
        ]);

        $bikes = Bike::accessibleBy($this->memberOfCommunity)
            ->orderBy("id")
            ->pluck("id");
        $this->assertEquals(0, $bikes->count());

        $trailers = Trailer::accessibleBy($this->memberOfCommunity)
            ->orderBy("id")
            ->pluck("id");
        $this->assertEquals(1, $trailers->count());

        $cars = Car::accessibleBy($this->memberOfCommunity)
            ->orderBy("id")
            ->pluck("id");
        $this->assertEquals(1, $cars->count());

        $cars = Car::accessibleBy($this->otherMemberOfCommunity)
            ->orderBy("id")
            ->pluck("id");
        $this->assertEquals(0, $cars->count());

        $this->community
            ->users()
            ->updateExistingPivot($this->otherMemberOfCommunity->id, [
                "approved_at" => new \DateTime(),
            ]);

        $this->otherMemberOfCommunity = $this->otherMemberOfCommunity->fresh();

        $cars = Car::accessibleBy($this->otherMemberOfCommunity)
            ->orderBy("id")
            ->pluck("id");
        $this->assertEquals(0, $cars->count());

        $borrower = new Borrower();
        $borrower->user()->associate($this->otherMemberOfCommunity);
        $borrower->approved_at = new \DateTime();
        $borrower->save();

        $this->otherMemberOfCommunity = $this->otherMemberOfCommunity->fresh();

        $cars = Car::accessibleBy($this->otherMemberOfCommunity)
            ->orderBy("id")
            ->pluck("id");
        $this->assertEquals(1, $cars->count());
    }

    public function testLoanableAccessibleDownFromBorough()
    {
        $loanables = Loanable::accessibleBy($this->memberOfCommunity)->pluck(
            "id"
        );
        $this->assertEquals(1, $loanables->count());
        $this->assertEquals(
            $this->memberOfCommunity->loanables()->first()->id,
            $loanables->first()
        );

        $boroughLoanable = factory(Trailer::class)->create([
            "owner_id" => $this->memberOfBorough->owner->id,
        ]);

        $loanables = Loanable::accessibleBy($this->memberOfCommunity)
            ->orderBy("id")
            ->pluck("id");
        $this->assertEquals(2, $loanables->count());
        $this->assertEquals(
            $this->memberOfCommunity->loanables()->first()->id,
            $loanables[0]
        );
        $this->assertEquals(
            $this->memberOfBorough->loanables()->first()->id,
            $loanables[1]
        );
    }

    public function testLoanableIsAccessibleUpFromBoroughIfEnabled()
    {
        $boroughLoanable = factory(Trailer::class)->create([
            "owner_id" => $this->memberOfBorough->owner->id,
        ]);

        $loanables = Loanable::accessibleBy($this->memberOfBorough)->pluck(
            "id"
        );
        $this->assertEquals(1, $loanables->count());
        $this->assertEquals(
            $this->memberOfBorough->loanables[0]->id,
            $loanables[0]
        );

        $loanable = Trailer::find($this->memberOfCommunity->loanables[0]->id);
        $loanable->share_with_parent_communities = true;
        $loanable->save();

        $loanables = Loanable::accessibleBy($this->memberOfBorough)
            ->where("id", "!=", $this->memberOfBorough->loanables[0]->id)
            ->pluck("id");
        $this->assertEquals(1, $loanables->count());
        $this->assertEquals($loanable->id, $loanables[0]);
    }

    public function testLoanableMethodGetCommunityForLoanBy()
    {
        $bike = factory(Bike::class)->create([
            "owner_id" => $this->memberOfCommunity->owner->id,
            "community_id" => $this->community->id,
        ]);

        // User is not approved on the loanable community
        // (you normally wouldn't be able to see the loanable to begin with)
        $community = $bike->getCommunityForLoanBy(
            $this->otherMemberOfCommunity
        );
        $this->assertEquals(null, $community);

        // User is approved on the loanable community
        $this->community
            ->users()
            ->updateExistingPivot($this->otherMemberOfCommunity->id, [
                "approved_at" => new \DateTime(),
            ]);

        $this->otherMemberOfCommunity = $this->otherMemberOfCommunity->fresh();

        $community = $bike->getCommunityForLoanBy(
            $this->otherMemberOfCommunity
        );
        $this->assertEquals($this->community->id, $community->id);

        // User is approved on a child community of the loanable community
        $bike->community_id = $this->borough->id;
        $bike->save();
        $bike = $bike->fresh();
        $community = $bike->getCommunityForLoanBy(
            $this->otherMemberOfCommunity
        );
        $this->assertEquals($this->community->id, $community->id);

        // User is approved on community of the loanable's owner
        $bike->community_id = null;
        $bike->save();
        $bike = $bike->fresh();

        $community = $bike->getCommunityForLoanBy(
            $this->otherMemberOfCommunity
        );
        $this->assertEquals($this->community->id, $community->id);
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

        $canceledPrePaymentLoan = factory(Loan::class)
            ->states("withCompletedIntention", "withCanceledPrePayment")
            ->create([
                "borrower_id" => $user->borrower->id,
                "loanable_id" => $bike->id,
                "community_id" => $this->community->id,
                "departure_at" => "3000-10-11 10:10:00",
                "duration_in_minutes" => 60,
            ]);

        $confirmedLoan = factory(Loan::class)
            ->states("withCompletedIntention", "withInProcessPrePayment")
            ->create([
                "borrower_id" => $user->borrower->id,
                "loanable_id" => $bike->id,
                "community_id" => $this->community->id,
                "departure_at" => "3000-10-12 10:10:00",
                "duration_in_minutes" => 60,
            ]);
        $confirmedLoan->intention->status = "completed";
        $confirmedLoan->intention->save();
        $confirmedLoan = $confirmedLoan->fresh();

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

    public function testExceptionPeriodRange()
    {
        $bike = factory(Bike::class)
            ->create([
                "availability_json" => '[{"available":false,"type":"periodRange","scope":["2021-11-22","2021-11-23","2021-11-24"],"period":"00:00-23:59"}]',
            ]);

        $ics_elements = explode("\n", $bike->availability_ics);

        // get the UIDs from the generated ics

        $ics_uids = array();

        foreach($ics_elements as $element) {
            $found = strpos($element, "UID");
            if($found === 0) {
                array_push($ics_uids, str_replace("UID:", "", $element));
            }
        }

        // get DTSTAMP from the generated ics

        $ics_dtstamps = array();

        foreach($ics_elements as $element) {
            $found = strpos($element, "DTSTAMP");
            if($found === 0) {
                array_push($ics_dtstamps, str_replace("DTSTAMP:", "", $element));
            }
        }

        $str_expected = "BEGIN:VCALENDAR\r\n" .
            "VERSION:2.0\r\n" .
            "PRODID:locomotion.app/api/loanables/$bike->id.ics\r\n" .
            "X-WR-TIMEZONE:America/Montreal\r\n" .
            "BEGIN:VTIMEZONE\r\n" .
            "TZID:America/Montreal\r\n" .
            "X-LIC-LOCATION:America/Montreal\r\n" .
            "BEGIN:DAYLIGHT\r\n" .
            "TZNAME:EDT\r\n" .
            "TZOFFSETFROM:-0400\r\n" .
            "TZOFFSETTO:-0500\r\n".
            "DTSTART:20071104T020000\r\n".
            "RRULE:FREQ=YEARLY;INTERVAL=1;BYMONTH=3;BYDAY=2SU\r\n" .
            "END:DAYLIGHT\r\n" .
            "BEGIN:STANDARD\r\n" .
            "TZNAME:EST\r\n" .
            "TZOFFSETFROM:-0500\r\n" .
            "TZOFFSETTO:-0400\r\n" .
            "DTSTART:20070311T030000\r\n" .
            "RRULE:FREQ=YEARLY;INTERVAL=1;BYMONTH=10;BYDAY=2SU\r\n" .
            "END:STANDARD\r\n" .
            "END:VTIMEZONE\r\n" .
            "BEGIN:VEVENT\r\n" .
            "UID:$ics_uids[0]\n" .
            "DTSTART;VALUE=DATE:20211122\r\n" .
            "SEQUENCE:0\r\n" .
            "TRANSP:OPAQUE\r\n" .
            "DTEND;VALUE=DATE:20211123\r\n" .
            "CLASS:PUBLIC\r\n" .
            "X-MICROSOFT-CDO-ALLDAYEVENT:TRUE\r\n" .
            "DTSTAMP:$ics_dtstamps[0]\n" .
            "END:VEVENT\r\n" .
            "BEGIN:VEVENT\r\n" .
            "UID:$ics_uids[1]\n" .
            "DTSTART;VALUE=DATE:20211123\r\n" .
            "SEQUENCE:0\r\n" .
            "TRANSP:OPAQUE\r\n" .
            "DTEND;VALUE=DATE:20211124\r\n" .
            "CLASS:PUBLIC\r\n" .
            "X-MICROSOFT-CDO-ALLDAYEVENT:TRUE\r\n" .
            "DTSTAMP:$ics_dtstamps[1]\n" .
            "END:VEVENT\r\n" .
            "BEGIN:VEVENT\r\n" .
            "UID:$ics_uids[2]\n" .
            "DTSTART;VALUE=DATE:20211124\r\n" .
            "SEQUENCE:0\r\n" .
            "TRANSP:OPAQUE\r\n" .
            "DTEND;VALUE=DATE:20211125\r\n" .
            "CLASS:PUBLIC\r\n" .
            "X-MICROSOFT-CDO-ALLDAYEVENT:TRUE\r\n" .
            "DTSTAMP:$ics_dtstamps[2]\n" .
            "END:VEVENT\r\n" .
            "END:VCALENDAR";

        $this->assertEquals(
            $str_expected,
            $bike->availability_ics);
    }
}
