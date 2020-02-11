<?php

use App\Models\Loan;
use Illuminate\Support\Str;
use Faker\Generator as Faker; // $faker->dateTime($min = 'now', $timezone='UTC'),//$format = 'Y-m-d H:i:sO'

$factory->define(Loan::class, function (Faker $faker) {
    return [
        'departure_at' => now(),
        'duration' => $faker->randomNumber($nbDigits = null, $strict = false),
        'borrower_id' => 1,
    ];
});
