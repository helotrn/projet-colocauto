<?php

use App\Models\Intention;
use App\Models\Loan;
use App\Models\PrePayment;
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
        "borrower_id" => 1,
        "estimated_distance" => $faker->randomNumber($nbDigits = 4),
        "estimated_insurance" => $faker->randomNumber($nbDigits = 4),
        "estimated_price" => $faker->randomNumber($nbDigits = 4),
        "reason" => $faker->text,
        "message_for_owner" => $faker->paragraph,
        "platform_tip" => $faker->randomNumber($nbDigits = 4),
    ];
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
