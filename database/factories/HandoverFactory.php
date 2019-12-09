<?php

use App\Models\Handover;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(App\Handover::class, function (Faker $faker) {
    return [
        'status' => $faker->randomElement(['in_process', 'canceled', 'completed']),
        'mileage_end' => $faker->numberBetween($min = 0, $max = 300000),
        'fuel_end' => $faker->numberBetween($min = 0, $max = 100),
        'comments_by_borrower' => $faker->sentence,
        'comments_by_owner' => $faker->sentence,
        'purchases_amount' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 100000),
    ];
});
