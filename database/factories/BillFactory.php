<?php

use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Bill::class, function (Faker $faker) {
    return [
        'period' => $faker->word,
        'payment_method' => $faker->word,
        'total' => $faker->numberBetween($min = 0, $max = 300000),
        'paid_at' => Carbon::now(),
    ];
});
