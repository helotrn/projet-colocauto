<?php

namespace Tests\Unit\Models;

use App\Models\Bike;
use App\Models\Borrower;
use App\Models\Community;
use App\Models\Extension;
use App\Models\Handover;
use App\Models\Loan;
use App\Models\Owner;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;
use Carbon\CarbonImmutable;

class LoanTest extends TestCase
{
    // TODO: Move to a calendar helper (#1080).
    public function testGetCalendarDays()
    {
        // Loan starts before midnight
        $this->assertEquals(
            5,
            Loan::getCalendarDays(
                new CarbonImmutable("2022-04-16 23:45:00"),
                new CarbonImmutable("2022-04-20 12:30:00")
            )
        );

        // Loan starts at midnight
        $this->assertEquals(
            4,
            Loan::getCalendarDays(
                new CarbonImmutable("2022-04-17 00:00:00"),
                new CarbonImmutable("2022-04-20 12:30:00")
            )
        );

        // Loan starts after midnight
        $this->assertEquals(
            4,
            Loan::getCalendarDays(
                new CarbonImmutable("2022-04-17 00:15:00"),
                new CarbonImmutable("2022-04-20 12:30:00")
            )
        );

        // Loan ends before midnight
        $this->assertEquals(
            5,
            Loan::getCalendarDays(
                new CarbonImmutable("2022-04-16 12:30:00"),
                new CarbonImmutable("2022-04-20 23:45:00")
            )
        );

        // Loan ends at midnight
        $this->assertEquals(
            5,
            Loan::getCalendarDays(
                new CarbonImmutable("2022-04-16 12:00:00"),
                new CarbonImmutable("2022-04-21 00:00:00")
            )
        );

        // Loan ends after midnight
        $this->assertEquals(
            6,
            Loan::getCalendarDays(
                new CarbonImmutable("2022-04-16 12:15:00"),
                new CarbonImmutable("2022-04-21 00:15:00")
            )
        );

        // Loan spans some years.
        $this->assertEquals(
            4024,
            Loan::getCalendarDays(
                new CarbonImmutable("2014-04-16 12:15:00"),
                new CarbonImmutable("2025-04-21 22:15:00")
            )
        );

        // Loan starts and ends at the same time.
        $this->assertEquals(
            0,
            Loan::getCalendarDays(
                new CarbonImmutable("2022-04-16 12:15:00"),
                new CarbonImmutable("2022-04-16 12:15:00")
            )
        );

        // Loan ends before it starts.
        $this->assertEquals(
            0,
            Loan::getCalendarDays(
                new CarbonImmutable("2022-04-20 12:30:00"),
                new CarbonImmutable("2022-04-16 23:45:00")
            )
        );
    }

    public function testIsCancelableBy_Canceled_fails()
    {
        $loan = factory(Loan::class)
            ->states("withCompletedPrePayment")
            ->create();

        $loan->cancel();

        $this->assertFalse($loan->isCancelableBy($this->user));
    }

    public function testIsCancelableBy_Paid_fails()
    {
        $community = factory(Community::class)
            ->state("withDefault10DollarsPricing")
            ->create();

        $borrowerUser = factory(User::class)->create();
        $borrowerUser
            ->communities()
            ->attach($community->id, ["approved_at" => new \DateTime()]);
        $borrower = factory(Borrower::class)->create([
            "user_id" => $borrowerUser->id,
        ]);

        $ownerUser = factory(User::class)->create();
        $ownerUser
            ->communities()
            ->attach($community->id, ["approved_at" => new \DateTime()]);
        $owner = factory(Owner::class)->create(["user_id" => $ownerUser->id]);

        $loanable = factory(Bike::class)->create([
            "owner_id" => $owner->id,
        ]);

        $loan = factory(Loan::class)
            ->state("withAllStepsCompleted")
            ->create([
                "borrower_id" => $borrower->id,
                "loanable_id" => $loanable->id,
                "departure_at" => Carbon::now()->subMinutes(30),
                "final_price" => 10,
            ]);

        $loan->refresh();

        // Not cancelable even by admin
        $this->assertFalse($loan->isCancelableBy($this->user));
    }

    public function testIsCancelableBy_OngoingPaying_fails()
    {
        $community = factory(Community::class)
            ->state("withDefault10DollarsPricing")
            ->create();

        $borrowerUser = factory(User::class)->create();
        $borrowerUser
            ->communities()
            ->attach($community->id, ["approved_at" => new \DateTime()]);
        $borrower = factory(Borrower::class)->create([
            "user_id" => $borrowerUser->id,
        ]);

        $ownerUser = factory(User::class)->create();
        $ownerUser
            ->communities()
            ->attach($community->id, ["approved_at" => new \DateTime()]);
        $owner = factory(Owner::class)->create(["user_id" => $ownerUser->id]);

        $loanable = factory(Bike::class)->create([
            "owner_id" => $owner->id,
        ]);

        $loan = factory(Loan::class)
            ->state("withCompletedTakeover")
            ->create([
                "borrower_id" => $borrower->id,
                "loanable_id" => $loanable->id,
                "departure_at" => Carbon::now()->subMinutes(30),
            ]);

        $this->assertFalse($loan->isCancelableBy($borrower->user));
    }

    public function testIsCancelableBy_stranger_fails()
    {
        $otherUser = factory(User::class)->create();

        $loan = factory(Loan::class)
            ->state("withCompletedPrePayment")
            ->create();

        $this->assertFalse($loan->isCancelableBy($otherUser));
    }

    public function testIsCancelableBy_Free_succeeds()
    {
        $community = factory(Community::class)
            ->state("withDefaultFreePricing")
            ->create();

        $borrowerUser = factory(User::class)->create();
        $borrowerUser
            ->communities()
            ->attach($community->id, ["approved_at" => new \DateTime()]);
        $borrower = factory(Borrower::class)->create([
            "user_id" => $borrowerUser->id,
        ]);

        $ownerUser = factory(User::class)->create();
        $ownerUser
            ->communities()
            ->attach($community->id, ["approved_at" => new \DateTime()]);
        $owner = factory(Owner::class)->create(["user_id" => $ownerUser->id]);

        $loanable = factory(Bike::class)->create([
            "owner_id" => $owner->id,
        ]);

        $loan = factory(Loan::class)
            ->state("withCompletedTakeover")
            ->create([
                "borrower_id" => $borrower->id,
                "loanable_id" => $loanable->id,
                "departure_at" => Carbon::now()->subMinutes(30),
            ]);

        $this->assertTrue($loan->isCancelableBy($borrower->user));
        $this->assertTrue($loan->isCancelableBy($owner->user));
    }

    public function testIsCancelableBy_future_succeeds()
    {
        $community = factory(Community::class)
            ->state("withDefault10DollarsPricing")
            ->create();

        $borrowerUser = factory(User::class)->create();
        $borrowerUser
            ->communities()
            ->attach($community->id, ["approved_at" => new \DateTime()]);
        $borrower = factory(Borrower::class)->create([
            "user_id" => $borrowerUser->id,
        ]);

        $ownerUser = factory(User::class)->create();
        $ownerUser
            ->communities()
            ->attach($community->id, ["approved_at" => new \DateTime()]);
        $owner = factory(Owner::class)->create(["user_id" => $ownerUser->id]);

        $loanable = factory(Bike::class)->create([
            "owner_id" => $owner->id,
        ]);

        $loan = factory(Loan::class)
            ->state("withCompletedTakeover")
            ->create([
                "borrower_id" => $borrower->id,
                "loanable_id" => $loanable->id,
                "departure_at" => Carbon::now()->addMinutes(30),
            ]);

        $this->assertTrue($loan->isCancelableBy($borrower->user));
        $this->assertTrue($loan->isCancelableBy($owner->user));
    }

    public function testIsCancelableBy_untaken_succeeds()
    {
        $community = factory(Community::class)
            ->state("withDefault10DollarsPricing")
            ->create();

        $borrowerUser = factory(User::class)->create();
        $borrowerUser
            ->communities()
            ->attach($community->id, ["approved_at" => new \DateTime()]);
        $borrower = factory(Borrower::class)->create([
            "user_id" => $borrowerUser->id,
        ]);

        $ownerUser = factory(User::class)->create();
        $ownerUser
            ->communities()
            ->attach($community->id, ["approved_at" => new \DateTime()]);
        $owner = factory(Owner::class)->create(["user_id" => $ownerUser->id]);

        $loanable = factory(Bike::class)->create([
            "owner_id" => $owner->id,
        ]);

        $loan = factory(Loan::class)
            ->state("withInProcessTakeover")
            ->create([
                "borrower_id" => $borrower->id,
                "loanable_id" => $loanable->id,
                "departure_at" => Carbon::now()->subMinutes(30),
            ]);

        $this->assertTrue($loan->isCancelableBy($borrower->user));
        $this->assertTrue($loan->isCancelableBy($owner->user));
    }

    public function testIsCancelableBy_admin_succeeds()
    {
        $community = factory(Community::class)
            ->state("withDefault10DollarsPricing")
            ->create();

        $communityAdmin = factory(User::class)->create();
        $communityAdmin->communities()->attach($community->id, [
            "approved_at" => new \DateTime(),
            "role" => "admin",
        ]);

        $borrowerUser = factory(User::class)->create();
        $borrowerUser
            ->communities()
            ->attach($community->id, ["approved_at" => new \DateTime()]);
        $borrower = factory(Borrower::class)->create([
            "user_id" => $borrowerUser->id,
        ]);

        $ownerUser = factory(User::class)->create();
        $ownerUser
            ->communities()
            ->attach($community->id, ["approved_at" => new \DateTime()]);
        $owner = factory(Owner::class)->create(["user_id" => $ownerUser->id]);

        $loanable = factory(Bike::class)->create([
            "owner_id" => $owner->id,
        ]);

        $loan = factory(Loan::class)
            ->state("withCompletedTakeover")
            ->create([
                "borrower_id" => $borrower->id,
                "loanable_id" => $loanable->id,
                "departure_at" => Carbon::now()->subMinutes(30),
            ]);

        // $this->user is global admin
        $this->assertTrue($loan->isCancelableBy($this->user));
        $this->assertTrue($loan->isCancelableBy($communityAdmin));
    }

    public function testCancel_Now()
    {
        $loan = factory(Loan::class)
            ->states("withCompletedPrePayment")
            ->create();

        $this->assertFalse($loan->isCanceled());

        $loan->cancel();

        $this->assertTrue($loan->isCanceled());

        $this->assertNotNull($loan->canceled_at);
        $this->assertEquals("canceled", $loan->status);
    }

    public function testCancel_At()
    {
        $loan = factory(Loan::class)
            ->states("withCompletedPrePayment")
            ->create();

        $this->assertFalse($loan->isCanceled());

        $loan->cancel(new CarbonImmutable("2022-04-16 12:34:56"));

        $this->assertTrue($loan->isCanceled());

        $this->assertNotNull($loan->canceled_at);
        $this->assertEquals("canceled", $loan->status);
        $this->assertEquals("2022-04-16 12:34:56", $loan->canceled_at);
    }

    public function testIsCanceled_Status()
    {
        $loan = factory(Loan::class)
            ->states("withCompletedPrePayment")
            ->create();

        $this->assertFalse($loan->isCanceled());

        $loan->status = "canceled";

        $this->assertTrue($loan->isCanceled());
    }

    public function testIsCanceled_CanceledAt()
    {
        $loan = factory(Loan::class)
            ->states("withCompletedPrePayment")
            ->create();

        $this->assertFalse($loan->isCanceled());

        $loan->canceled_at = new CarbonImmutable();

        $this->assertTrue($loan->isCanceled());
    }

    public function testIsContested_TakeoverContested()
    {
        $loan = factory(Loan::class)
            ->states("withContestedTakeover")
            ->create();

        $this->assertTrue($loan->is_contested);
    }

    public function testIsContested_HandoverContested()
    {
        $loan = factory(Loan::class)
            ->states("withContestedHandover")
            ->create();

        $loan->refresh();
        $this->assertTrue($loan->is_contested);
    }

    public function testIsNotContested_OtherActionsCanceled()
    {
        $loan = factory(Loan::class)
            ->states(["withCanceledPrePayment", "withCanceledExtension"])
            ->create();

        $this->assertFalse($loan->is_contested);
    }

    public function testGetStatusFromActions_IntentionInProcess()
    {
        $loan = factory(Loan::class)
            ->states("withInProcessIntention")
            ->create();

        // Must refresh materialized views before asking for loan->actions.
        \DB::statement("REFRESH MATERIALIZED VIEW actions");

        // Refresh loan from database
        $loan = $loan->fresh();

        // Assert that loan is in_process.
        $this->assertEquals("in_process", $loan->getStatusFromActions());
    }

    public function testGetStatusFromActions_PaymentCompleted()
    {
        $loan = factory(Loan::class)
            ->states(["withAllStepsCompleted"])
            ->create();

        // Must refresh materialized views before asking for loan->actions.
        \DB::statement("REFRESH MATERIALIZED VIEW actions");

        // Refresh loan from database
        $loan = $loan->fresh();

        // Assert has payment completed.
        $this->assertEquals(
            "completed",
            $loan->payment ? $loan->payment->status : ""
        );

        // Assert that loan is completed.
        $this->assertEquals("completed", $loan->getStatusFromActions());
    }

    public function testLoanTimesAndDurations_IntentionInProcess()
    {
        // Milliseconds get truncated in the database, zero them out so as to
        // compare on seconds only.
        $departureAt = CarbonImmutable::now()
            ->setHours(4)
            ->setMinutes(30)
            ->setSeconds(0)
            ->setMilliseconds(0);

        $loan = factory(Loan::class)
            ->states("withInProcessIntention")
            ->create([
                "departure_at" => $departureAt,
                "duration_in_minutes" => 75,
            ]);

        // Must refresh materialized views before asking for loan->actions.
        \DB::statement("REFRESH MATERIALIZED VIEW actions");

        // Refresh loan from database
        $loan = $loan->fresh();

        // Validate loan actions
        $refActionStatuses = [["intention", "in_process"]];

        $testActionStatuses = [];
        foreach ($loan->actions as $action) {
            $testActionStatuses[] = [$action->type, $action->status];
        }

        $this->assertEquals($refActionStatuses, $testActionStatuses);

        // Validate loan return time.
        $this->assertTrue(
            $departureAt
                ->addMinutes(75)
                ->equalTo($loan->getActualReturnAtFromActions())
        );

        // Check the loan's actual duration.
        $this->assertEquals(75, $loan->actual_duration_in_minutes);

        // Check the loan's number of calendar days.
        $this->assertEquals(1, $loan->calendar_days);
    }

    public function testLoanTimesAndDurations_IntentionCompleted()
    {
        // Milliseconds get truncated in the database, zero them out so as to
        // compare on seconds only.
        $departureAt = CarbonImmutable::now()
            ->setHours(4)
            ->setMinutes(30)
            ->setSeconds(0)
            ->setMilliseconds(0);

        $loan = factory(Loan::class)
            ->states("withCompletedIntention")
            ->create([
                "departure_at" => $departureAt,
                "duration_in_minutes" => 75,
            ]);

        // Must refresh materialized views before asking for loan->actions.
        \DB::statement("REFRESH MATERIALIZED VIEW actions");

        // Refresh loan from database
        $loan = $loan->fresh();

        // Validate loan actions
        $refActionStatuses = [["intention", "completed"]];

        $testActionStatuses = [];
        foreach ($loan->actions as $action) {
            $testActionStatuses[] = [$action->type, $action->status];
        }

        $this->assertEquals($refActionStatuses, $testActionStatuses);

        // Validate loan return time.
        $this->assertTrue(
            $departureAt
                ->addMinutes(75)
                ->equalTo($loan->getActualReturnAtFromActions())
        );

        // Check the loan's actual duration.
        $this->assertEquals(75, $loan->actual_duration_in_minutes);

        // Check the loan's number of calendar days.
        $this->assertEquals(1, $loan->calendar_days);
    }

    public function testLoanTimesAndDurations_ExtensionInProcess()
    {
        // Milliseconds get truncated in the database, zero them out so as to
        // compare on seconds only.
        $departureAt = CarbonImmutable::now()
            ->setHours(4)
            ->setMinutes(30)
            ->setSeconds(0)
            ->setMilliseconds(0);

        $loan = factory(Loan::class)
            ->states([
                "withCompletedIntention",
                "withCompletedPrePayment",
                "withCompletedTakeover",
            ])
            ->create([
                "departure_at" => $departureAt,
                "duration_in_minutes" => 75,
            ]);

        // Extension in process
        $loan->extensions()->save(
            factory(Extension::class)->make([
                "new_duration" => 120,
                "status" => "in_process",
            ])
        );

        // Must refresh materialized views before asking for loan->actions.
        \DB::statement("REFRESH MATERIALIZED VIEW actions");

        // Refresh loan from database
        $loan = $loan->fresh();

        // Validate loan actions
        $refActionStatuses = [
            ["intention", "completed"],
            ["pre_payment", "completed"],
            ["takeover", "completed"],
            ["extension", "in_process"],
        ];

        $testActionStatuses = [];
        foreach ($loan->actions as $action) {
            $testActionStatuses[] = [$action->type, $action->status];
        }

        $this->assertEquals($refActionStatuses, $testActionStatuses);

        // Assert that the loan return time was not changed
        $this->assertTrue(
            $departureAt
                ->addMinutes(75)
                ->equalTo($loan->getActualReturnAtFromActions())
        );

        // Check the loan's actual duration.
        $this->assertEquals(75, $loan->actual_duration_in_minutes);

        // Check the loan's number of calendar days.
        $this->assertEquals(1, $loan->calendar_days);
    }

    public function testLoanTimesAndDurations_ExtensionsCompleted()
    {
        // Milliseconds get truncated in the database, zero them out so as to
        // compare on seconds only.
        $departureAt = CarbonImmutable::now()
            ->setHours(4)
            ->setMinutes(30)
            ->setSeconds(0)
            ->setMilliseconds(0);

        $loan = factory(Loan::class)
            ->states([
                "withCompletedIntention",
                "withCompletedPrePayment",
                "withCompletedTakeover",
            ])
            ->create([
                "departure_at" => $departureAt,
                "duration_in_minutes" => 75,
            ]);

        // Completed extensions
        $loan->extensions()->save(
            factory(Extension::class)->make([
                "new_duration" => 120,
                "status" => "completed",
                "executed_at" => $departureAt->addMinutes(15),
            ])
        );

        $loan->extensions()->save(
            factory(Extension::class)->make([
                "new_duration" => 150,
                "status" => "completed",
                "executed_at" => $departureAt->addMinutes(15),
            ])
        );

        // Must refresh materialized views before asking for loan->actions.
        \DB::statement("REFRESH MATERIALIZED VIEW actions");

        // Refresh loan from database
        $loan = $loan->fresh();

        // Validate loan actions
        $refActionStatuses = [
            ["intention", "completed"],
            ["pre_payment", "completed"],
            ["takeover", "completed"],
            ["extension", "completed"],
            ["extension", "completed"],
        ];

        $testActionStatuses = [];
        foreach ($loan->actions as $action) {
            $testActionStatuses[] = [$action->type, $action->status];
        }

        $this->assertEquals($refActionStatuses, $testActionStatuses);

        // Assert that the loan return time accounts for extensions.
        $this->assertTrue(
            $departureAt
                ->addMinutes(150)
                ->equalTo($loan->getActualReturnAtFromActions())
        );

        // Check the loan's actual duration.
        $this->assertEquals(150, $loan->actual_duration_in_minutes);

        // Check the loan's number of calendar days.
        $this->assertEquals(1, $loan->calendar_days);
    }

    public function testLoanTimesAndDurations_ExtensionRejected()
    {
        // Milliseconds get truncated in the database, zero them out so as to
        // compare on seconds only.
        $departureAt = CarbonImmutable::now()
            ->setHours(4)
            ->setMinutes(30)
            ->setSeconds(0)
            ->setMilliseconds(0);

        $loan = factory(Loan::class)
            ->states([
                "withCompletedIntention",
                "withCompletedPrePayment",
                "withCompletedTakeover",
            ])
            ->create([
                "departure_at" => $departureAt,
                "duration_in_minutes" => 75,
            ]);

        // Rejected extension
        $loan->extensions()->save(
            factory(Extension::class)->make([
                "new_duration" => 120,
                "status" => "rejected",
                "executed_at" => $departureAt->addMinutes(15),
            ])
        );

        // Must refresh materialized views before asking for loan->actions.
        \DB::statement("REFRESH MATERIALIZED VIEW actions");

        // Refresh loan from database
        $loan = $loan->fresh();

        // Validate loan actions
        $refActionStatuses = [
            ["intention", "completed"],
            ["pre_payment", "completed"],
            ["takeover", "completed"],
            ["extension", "rejected"],
        ];

        $testActionStatuses = [];
        foreach ($loan->actions as $action) {
            $testActionStatuses[] = [$action->type, $action->status];
        }

        $this->assertEquals($refActionStatuses, $testActionStatuses);

        // Assert that the loan return time accounts is not impacted by rejected extensions.
        $this->assertTrue(
            $departureAt
                ->addMinutes(75)
                ->equalTo($loan->getActualReturnAtFromActions())
        );

        // Check the loan's actual duration.
        $this->assertEquals(75, $loan->actual_duration_in_minutes);

        // Check the loan's number of calendar days.
        $this->assertEquals(1, $loan->calendar_days);
    }

    public function testLoanTimesAndDurations_ExtensionCanceled()
    {
        // Milliseconds get truncated in the database, zero them out so as to
        // compare on seconds only.
        $departureAt = CarbonImmutable::now()
            ->setHours(4)
            ->setMinutes(30)
            ->setSeconds(0)
            ->setMilliseconds(0);

        $loan = factory(Loan::class)
            ->states([
                "withCompletedIntention",
                "withCompletedPrePayment",
                "withCompletedTakeover",
            ])
            ->create([
                "departure_at" => $departureAt,
                "duration_in_minutes" => 75,
            ]);

        // Canceled extension
        $loan->extensions()->save(
            factory(Extension::class)->make([
                "new_duration" => 120,
                "status" => "canceled",
                "executed_at" => $departureAt->addMinutes(15),
            ])
        );

        // Must refresh materialized views before asking for loan->actions.
        \DB::statement("REFRESH MATERIALIZED VIEW actions");

        // Refresh loan from database
        $loan = $loan->fresh();

        // Validate loan actions
        $refActionStatuses = [
            ["intention", "completed"],
            ["pre_payment", "completed"],
            ["takeover", "completed"],
            ["extension", "canceled"],
        ];

        $testActionStatuses = [];
        foreach ($loan->actions as $action) {
            $testActionStatuses[] = [$action->type, $action->status];
        }

        $this->assertEquals($refActionStatuses, $testActionStatuses);

        // Assert that the loan return time accounts is not impacted by canceled extensions.
        $this->assertTrue(
            $departureAt
                ->addMinutes(75)
                ->equalTo($loan->getActualReturnAtFromActions())
        );

        // Check the loan's actual duration.
        $this->assertEquals(75, $loan->actual_duration_in_minutes);

        // Check the loan's number of calendar days.
        $this->assertEquals(1, $loan->calendar_days);
    }

    public function testLoanTimesAndDurations_EarlyPayment()
    {
        // Milliseconds get truncated in the database, zero them out so as to
        // compare on seconds only.
        $departureAt = CarbonImmutable::now()
            ->setHours(4)
            ->setMinutes(30)
            ->setSeconds(0)
            ->setMilliseconds(0);

        $loan = factory(Loan::class)
            ->states([
                "withCompletedIntention",
                "withCompletedPrePayment",
                "withCompletedTakeover",
            ])
            ->create([
                "departure_at" => $departureAt,
                "duration_in_minutes" => 75,
            ]);

        // Completed handover
        $loan->handover()->save(
            factory(Handover::class)->make([
                "status" => "completed",
                "executed_at" => $departureAt->addMinutes(45),
            ])
        );

        // Completed payment
        $loan->payment()->save(
            factory(Payment::class)->make([
                "status" => "completed",
                "executed_at" => $departureAt->addMinutes(60),
            ])
        );

        // Must refresh materialized views before asking for loan->actions.
        \DB::statement("REFRESH MATERIALIZED VIEW actions");

        // Refresh loan from database
        $loan = $loan->fresh();

        // Validate loan actions
        $refActionStatuses = [
            ["intention", "completed"],
            ["pre_payment", "completed"],
            ["takeover", "completed"],
            ["handover", "completed"],
            ["payment", "completed"],
        ];

        $testActionStatuses = [];
        foreach ($loan->actions as $action) {
            $testActionStatuses[] = [$action->type, $action->status];
        }

        $this->assertEquals($refActionStatuses, $testActionStatuses);

        // Assert that the loan return time accounts for early payment.
        $this->assertTrue(
            $departureAt
                ->addMinutes(60)
                ->equalTo($loan->getActualReturnAtFromActions())
        );

        // Check the loan's actual duration.
        $this->assertEquals(60, $loan->actual_duration_in_minutes);

        // Check the loan's number of calendar days.
        $this->assertEquals(1, $loan->calendar_days);
    }

    public function testLoanTimesAndDurations_EarlyPaymentWithAcceptedExtension()
    {
        // Milliseconds get truncated in the database, zero them out so as to
        // compare on seconds only.
        $departureAt = CarbonImmutable::now()
            ->setHours(4)
            ->setMinutes(30)
            ->setSeconds(0)
            ->setMilliseconds(0);

        $loan = factory(Loan::class)
            ->states([
                "withCompletedIntention",
                "withCompletedPrePayment",
                "withCompletedTakeover",
            ])
            ->create([
                "departure_at" => $departureAt,
                "duration_in_minutes" => 75,
            ]);

        // Accepted extension
        $loan->extensions()->save(
            factory(Extension::class)->make([
                "new_duration" => 120,
                "status" => "completed",
                "executed_at" => $departureAt->addMinutes(15),
            ])
        );

        // Completed handover
        $loan->handover()->save(
            factory(Handover::class)->make([
                "status" => "completed",
                "executed_at" => $departureAt->addMinutes(90),
            ])
        );

        // Completed payment
        $loan->payment()->save(
            factory(Payment::class)->make([
                "status" => "completed",
                "executed_at" => $departureAt->addMinutes(105),
            ])
        );

        // Must refresh materialized views before asking for loan->actions.
        \DB::statement("REFRESH MATERIALIZED VIEW actions");

        // Refresh loan from database
        $loan = $loan->fresh();

        // Validate loan actions
        $refActionStatuses = [
            ["intention", "completed"],
            ["pre_payment", "completed"],
            ["takeover", "completed"],
            ["extension", "completed"],
            ["handover", "completed"],
            ["payment", "completed"],
        ];

        $testActionStatuses = [];
        foreach ($loan->actions as $action) {
            $testActionStatuses[] = [$action->type, $action->status];
        }

        $this->assertEquals($refActionStatuses, $testActionStatuses);

        // Assert that the loan return time accounts for early payment with extensions.
        $this->assertTrue(
            $departureAt
                ->addMinutes(105)
                ->equalTo($loan->getActualReturnAtFromActions())
        );

        // Check the loan's actual duration.
        $this->assertEquals(105, $loan->actual_duration_in_minutes);

        // Check the loan's number of calendar days.
        $this->assertEquals(1, $loan->calendar_days);
    }

    public function testLoanTimesAndDurations_ShortSpanOverTwoCalendarDays()
    {
        // Milliseconds get truncated in the database, zero them out so as to
        // compare on seconds only.
        $departureAt = CarbonImmutable::now()
            ->setHours(23)
            ->setMinutes(30)
            ->setSeconds(0)
            ->setMilliseconds(0);

        $loan = factory(Loan::class)
            ->states(["withCompletedIntention"])
            ->create([
                "departure_at" => $departureAt,
                "duration_in_minutes" => 60,
            ]);

        // Must refresh materialized views before asking for loan->actions.
        \DB::statement("REFRESH MATERIALIZED VIEW actions");

        // Refresh loan from database
        $loan = $loan->fresh();

        // Validate loan actions
        $refActionStatuses = [["intention", "completed"]];

        $testActionStatuses = [];
        foreach ($loan->actions as $action) {
            $testActionStatuses[] = [$action->type, $action->status];
        }

        $this->assertEquals($refActionStatuses, $testActionStatuses);

        // Validate loan return time.
        $this->assertTrue(
            $departureAt
                ->addMinutes(60)
                ->equalTo($loan->getActualReturnAtFromActions())
        );

        // Check the loan's actual duration.
        $this->assertEquals(60, $loan->actual_duration_in_minutes);

        // Check the loan's number of calendar days.
        $this->assertEquals(2, $loan->calendar_days);
    }

    public function testLoanTimesAndDurations_MultipleCalendarDays()
    {
        // Milliseconds get truncated in the database, zero them out so as to
        // compare on seconds only.
        $departureAt = CarbonImmutable::now()
            ->setHours(4)
            ->setMinutes(30)
            ->setSeconds(0)
            ->setMilliseconds(0);

        $loan = factory(Loan::class)
            ->states(["withCompletedIntention"])
            ->create([
                "departure_at" => $departureAt,
                // Arbitrarily more than 3 days. Loan then spans onto 4 calendar days.
                "duration_in_minutes" => 3 * 24 * 60 + 415,
            ]);

        // Must refresh materialized views before asking for loan->actions.
        \DB::statement("REFRESH MATERIALIZED VIEW actions");

        // Refresh loan from database
        $loan = $loan->fresh();

        // Validate loan actions
        $refActionStatuses = [["intention", "completed"]];

        $testActionStatuses = [];
        foreach ($loan->actions as $action) {
            $testActionStatuses[] = [$action->type, $action->status];
        }

        $this->assertEquals($refActionStatuses, $testActionStatuses);

        // Validate loan return time.
        $this->assertTrue(
            $departureAt
                ->addMinutes(4735)
                ->equalTo($loan->getActualReturnAtFromActions())
        );

        // Check the loan's actual duration.
        $this->assertEquals(4735, $loan->actual_duration_in_minutes);

        // Check the loan's number of calendar days.
        $this->assertEquals(4, $loan->calendar_days);
    }

    public function testLoanTimesAndDurations_MultipleCalendarDaysWithAcceptedExtension()
    {
        // Milliseconds get truncated in the database, zero them out so as to
        // compare on seconds only.
        $departureAt = CarbonImmutable::now()
            ->setHours(4)
            ->setMinutes(30)
            ->setSeconds(0)
            ->setMilliseconds(0);

        $loan = factory(Loan::class)
            ->states([
                "withCompletedIntention",
                "withCompletedPrePayment",
                "withCompletedTakeover",
            ])
            ->create([
                "departure_at" => $departureAt,
                "duration_in_minutes" => 24 * 60 + 345,
            ]);

        // Accepted extension
        $loan->extensions()->save(
            factory(Extension::class)->make([
                "new_duration" => 3 * 24 * 60 + 415,
                "status" => "completed",
                "executed_at" => $departureAt->addMinutes(15),
            ])
        );

        // Must refresh materialized views before asking for loan->actions.
        \DB::statement("REFRESH MATERIALIZED VIEW actions");

        // Refresh loan from database
        $loan = $loan->fresh();

        // Validate loan actions
        $refActionStatuses = [
            ["intention", "completed"],
            ["pre_payment", "completed"],
            ["takeover", "completed"],
            ["extension", "completed"],
        ];

        $testActionStatuses = [];
        foreach ($loan->actions as $action) {
            $testActionStatuses[] = [$action->type, $action->status];
        }

        $this->assertEquals($refActionStatuses, $testActionStatuses);

        // Assert that the loan return time accounts for early payment with extensions.
        $this->assertTrue(
            $departureAt
                ->addMinutes(4735)
                ->equalTo($loan->getActualReturnAtFromActions())
        );

        // Check the loan's actual duration.
        $this->assertEquals(4735, $loan->actual_duration_in_minutes);

        // Check the loan's number of calendar days.
        $this->assertEquals(4, $loan->calendar_days);
    }

    public function testLoanTimesAndDurations_MultipleCalendarDaysAndEarlyPayment()
    {
        // Milliseconds get truncated in the database, zero them out so as to
        // compare on seconds only.
        $departureAt = CarbonImmutable::now()
            ->setHours(4)
            ->setMinutes(30)
            ->setSeconds(0)
            ->setMilliseconds(0);

        $loan = factory(Loan::class)
            ->states([
                "withCompletedIntention",
                "withCompletedPrePayment",
                "withCompletedTakeover",
            ])
            ->create([
                "departure_at" => $departureAt,
                // Arbitrarily more than 3 days. Loan then spans onto 4 calendar days.
                "duration_in_minutes" => 3 * 24 * 60 + 415,
            ]);

        // Completed handover
        $loan->handover()->save(
            factory(Handover::class)->make([
                "status" => "completed",
                "executed_at" => $departureAt->addMinutes(90),
            ])
        );

        // Completed payment
        $loan->payment()->save(
            factory(Payment::class)->make([
                "status" => "completed",
                "executed_at" => $departureAt->addMinutes(2 * 24 * 60 + 675),
            ])
        );

        // Must refresh materialized views before asking for loan->actions.
        \DB::statement("REFRESH MATERIALIZED VIEW actions");

        // Refresh loan from database
        $loan = $loan->fresh();

        // Validate loan actions
        $refActionStatuses = [
            ["intention", "completed"],
            ["pre_payment", "completed"],
            ["takeover", "completed"],
            ["handover", "completed"],
            ["payment", "completed"],
        ];

        $testActionStatuses = [];
        foreach ($loan->actions as $action) {
            $testActionStatuses[] = [$action->type, $action->status];
        }

        $this->assertEquals($refActionStatuses, $testActionStatuses);

        // Assert that the loan return time accounts for early payment with extensions.
        $this->assertTrue(
            $departureAt
                ->addMinutes(3555)
                ->equalTo($loan->getActualReturnAtFromActions())
        );

        // Check the loan's actual duration.
        $this->assertEquals(3555, $loan->actual_duration_in_minutes);

        // Check the loan's number of calendar days.
        $this->assertEquals(3, $loan->calendar_days);
    }

    public function testLoanTimesAndDurations_MultipleCalendarDaysEarlyPaymentWithAcceptedExtension()
    {
        // Milliseconds get truncated in the database, zero them out so as to
        // compare on seconds only.
        $departureAt = CarbonImmutable::now()
            ->setHours(4)
            ->setMinutes(30)
            ->setSeconds(0)
            ->setMilliseconds(0);

        $loan = factory(Loan::class)
            ->states([
                "withCompletedIntention",
                "withCompletedPrePayment",
                "withCompletedTakeover",
            ])
            ->create([
                "departure_at" => $departureAt,
                "duration_in_minutes" => 24 * 60 + 345,
            ]);

        // Accepted extension
        $loan->extensions()->save(
            factory(Extension::class)->make([
                "new_duration" => 3 * 24 * 60 + 415,
                "status" => "completed",
                "executed_at" => $departureAt->addMinutes(15),
            ])
        );

        // Completed handover
        $loan->handover()->save(
            factory(Handover::class)->make([
                "status" => "completed",
                "executed_at" => $departureAt->addMinutes(90),
            ])
        );

        // Completed payment
        $loan->payment()->save(
            factory(Payment::class)->make([
                "status" => "completed",
                "executed_at" => $departureAt->addMinutes(2 * 24 * 60 + 675),
            ])
        );

        // Must refresh materialized views before asking for loan->actions.
        \DB::statement("REFRESH MATERIALIZED VIEW actions");

        // Refresh loan from database
        $loan = $loan->fresh();

        // Validate loan actions
        $refActionStatuses = [
            ["intention", "completed"],
            ["pre_payment", "completed"],
            ["takeover", "completed"],
            ["extension", "completed"],
            ["handover", "completed"],
            ["payment", "completed"],
        ];

        $testActionStatuses = [];
        foreach ($loan->actions as $action) {
            $testActionStatuses[] = [$action->type, $action->status];
        }

        $this->assertEquals($refActionStatuses, $testActionStatuses);

        // Assert that the loan return time accounts for early payment with extensions.
        $this->assertTrue(
            $departureAt
                ->addMinutes(3555)
                ->equalTo($loan->getActualReturnAtFromActions())
        );

        // Check the loan's actual duration.
        $this->assertEquals(3555, $loan->actual_duration_in_minutes);

        // Check the loan's number of calendar days.
        $this->assertEquals(3, $loan->calendar_days);
    }
}
