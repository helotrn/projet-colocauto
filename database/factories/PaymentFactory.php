<?php

use App\Models\Payment;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Payment::class, function (Faker $faker) {
    return [
        "executed_at" => Carbon::now(),
        "status" => $faker->randomElement([
            "in_process",
            "canceled",
            "completed",
        ]),
    ];
});
