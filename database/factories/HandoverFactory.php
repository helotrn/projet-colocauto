<?php

use App\Models\Handover;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Handover::class, function (Faker $faker) {
    return [
        "executed_at" => Carbon::now(),
        "status" => $faker->randomElement([
            "in_process",
            "canceled",
            "completed",
        ]),
        "mileage_end" => $faker->numberBetween($min = 0, $max = 300000),
        "comments_by_borrower" => $faker->sentence,
        "comments_by_owner" => $faker->sentence,
        "purchases_amount" => $faker->randomFloat(
            $nbMaxDecimals = 2,
            $min = 0,
            $max = 100000
        ),
        "contested_at" => null,
        "comments_on_contestation" => null,
    ];
});
