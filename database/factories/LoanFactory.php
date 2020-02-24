<?php

use App\Models\Loan;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Loan::class, function (Faker $faker) {
    return [
        'departure_at' => Carbon::now(),
        'duration_in_minutes' => $faker->randomNumber($nbDigits = null, $strict = false),
        'borrower_id' => 1,
    ];
});
