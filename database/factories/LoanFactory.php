<?php

use App\Models\Loan;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Loan::class, function (Faker $faker) {
    return [
        'duration' => $faker->randomNumber($nbDigits = null, $strict = false),
    ];
});
