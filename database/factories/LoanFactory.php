<?php

use App\Models\Bike;
use App\Models\Borrower;
use App\Models\Extension;
use App\Models\Handover;
use App\Models\Intention;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\PrePayment;
use App\Models\Takeover;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Loan::class, function (Faker $faker) {
    return [
        "departure_at" => Carbon::now(),
        "duration_in_minutes" => $faker->randomNumber(
            $nbDigits = null,
            $strict = false
        ),
        "estimated_distance" => $faker->randomNumber($nbDigits = 4),
        "estimated_insurance" => $faker->randomNumber($nbDigits = 4),
        "estimated_price" => $faker->randomNumber($nbDigits = 4),
        "reason" => $faker->text,
        "message_for_owner" => $faker->paragraph,
        "platform_tip" => $faker->randomNumber($nbDigits = 4),
    ];
});

$factory->afterMaking(Loan::class, function ($loan) {
    if (!$loan->loanable_id) {
        $loanable = factory(Bike::class)
            ->states("withCommunity")
            ->create();
        $loan->loanable_id = $loanable->id;
        $loan->community_id = $loanable->community_id;
    }

    if (!$loan->borrower_id) {
        $borrower = factory(Borrower::class)->create();
        $loan->borrower_id = $borrower->id;
    }
});

$factory->afterCreatingState(Loan::class, "withCompletedIntention", function (
    $loan,
    $faker
) {
    $loan->intention()->save(
        factory(Intention::class)->make([
            "status" => "completed",
        ])
    );
});

$factory->afterCreatingState(Loan::class, "withInProcessIntention", function (
    $loan,
    $faker
) {
    $loan->intention()->save(
        factory(Intention::class)->make([
            "status" => "in_process",
        ])
    );
});

$factory->afterCreatingState(Loan::class, "withInProcessPrePayment", function (
    $loan,
    $faker
) {
    $loan->prePayment()->save(
        factory(PrePayment::class)->make([
            "status" => "in_process",
        ])
    );
});

$factory->afterCreatingState(Loan::class, "withCanceledPrePayment", function (
    $loan,
    $faker
) {
    $loan->prePayment()->save(
        factory(PrePayment::class)->make([
            "status" => "canceled",
        ])
    );
});

$factory->afterCreatingState(Loan::class, "withCompletedExtension", function (
    $loan,
    $faker
) {
    $loan->extensions()->save(
        factory(Extension::class)->make([
            "new_duration" => 120,
            "status" => "completed",
        ])
    );
});

$factory->afterCreatingState(Loan::class, "withAllStepsCompleted", function (
    $loan
) {
    return $loan::withoutEvents(function () use ($loan) {
        $loan->intention()->save(
            factory(Intention::class)->make([
                "status" => "completed",
            ])
        );

        $loan->prePayment()->save(
            factory(PrePayment::class)->make([
                "status" => "completed",
            ])
        );

        $loan->takeover()->save(
            factory(Takeover::class)->make([
                "status" => "completed",
            ])
        );

        $loan->handover()->save(
            factory(Handover::class)->make([
                "status" => "completed",
            ])
        );

        $loan->payment()->save(
            factory(Payment::class)->make([
                "executed_at" => Carbon::now()->add(100, "years"),
                "status" => "completed",
            ])
        );
    });
});
