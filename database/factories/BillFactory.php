<?php

use App\Models\Bill;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(App\Bill::class, function (Faker $faker) {
    return [
        'period' => $faker->word,
        'payment_method' => $faker->word,
        'total' => $faker->numberBetween($min = 0, $max = 300000),
    ];
});
