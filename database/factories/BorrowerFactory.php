<?php

use App\Models\Borrower;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Borrower::class, function (Faker $faker) {
    return [
        'drivers_license_number' => $faker->numberBetween($min = 1111111111, $max = 999999999),
        'has_been_sued_last_ten_years' => $faker->boolean,
        'noke_id' => $faker->numberBetween($min = 000000000, $max = 999999999),
    ];
});
