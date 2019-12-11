<?php

use App\Models\Loan;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Loan::class, function (Faker $faker) {
    return [
        'departure_at' => $faker->dateTime($format = 'Y-m-d H:i:sO', $max = 'now'),
        'duration' => $faker->randomNumber($nbDigits = null, $strict = false),
    ];
});
