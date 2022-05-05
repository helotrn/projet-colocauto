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
            "executed_at" => Carbon::now(),
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

$factory->afterCreatingState(Loan::class, "withInProcessTakeover", function (
    $loan,
    $faker
) {
    $loan->takeover()->save(
        factory(Takeover::class)->make([
            "status" => "in_process",
        ])
    );
});

$factory->afterCreatingState(Loan::class, "withCompletedTakeover", function (
    $loan,
    $faker
) {
    $loan->takeover()->save(
        factory(Takeover::class)->make([
            "status" => "completed",
        ])
    );
});

$factory->afterCreatingState(Loan::class, "withContestedTakeover", function (
    $loan,
    $faker
) {
    $loan->takeover()->save(
        factory(Takeover::class)->make([
            "status" => "canceled",
            "executed_at" => Carbon::now(),
        ])
    );
});

$factory->afterCreatingState(Loan::class, "withInProcessHandover", function (
    $loan,
    $faker
) {
    $loan->handover()->save(
        factory(Handover::class)->make([
            "status" => "in_process",
        ])
    );
});
$factory->afterCreatingState(Loan::class, "withCompletedHandover", function (
    $loan,
    $faker
) {
    $loan->handover()->save(
        factory(Handover::class)->make([
            "status" => "completed",
            "executed_at" => Carbon::now(),
        ])
    );
});

$factory->afterCreatingState(Loan::class, "withContestedHandover", function (
    $loan,
    $faker
) {
    $loan->handover()->save(
        factory(Handover::class)->make([
            "status" => "canceled",
            "executed_at" => Carbon::now(),
        ])
    );
});

$factory->afterCreatingState(Loan::class, "withCompletedPrePayment", function (
    $loan,
    $faker
) {
    $loan->prePayment()->save(
        factory(PrePayment::class)->make([
            "executed_at" => Carbon::now(),
            "status" => "completed",
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
            "executed_at" => Carbon::now(),
        ])
    );
});

$factory->afterCreatingState(Loan::class, "withInProcessPayment", function (
    $loan,
    $faker
) {
    $loan->payment()->save(
        factory(Payment::class)->make([
            "status" => "in_process",
        ])
    );
});

$factory->afterCreatingState(Loan::class, "withCanceledHandover", function (
    Loan $loan,
    Faker $faker
) {
    $loan->handover()->save(
        factory(Handover::class)->make([
            "status" => "canceled",
            "executed_at" => Carbon::now(),
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
                "executed_at" => Carbon::now(),
            ])
        );

        $loan->prePayment()->save(
            factory(PrePayment::class)->make([
                "status" => "completed",
                "executed_at" => Carbon::now(),
            ])
        );

        $loan->takeover()->save(
            factory(Takeover::class)->make([
                "status" => "completed",
                "executed_at" => Carbon::now(),
            ])
        );

        $loan->handover()->save(
            factory(Handover::class)->make([
                "status" => "completed",
                "executed_at" => Carbon::now(),
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

$factory->afterCreatingState(Loan::class, "butPaymentInProcess", function (
    Loan $loan,
    Faker $faker
) {
    if (!$loan->payment) {
        $loan->payment()->save(factory(Payment::class)->make());
    }

    $payment = $loan->payment()->first();

    $payment->status = "in_process";
    $payment->executed_at = null;

    $payment->save();
});
