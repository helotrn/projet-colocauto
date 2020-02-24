<?php

use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Bill::class, function (Faker $faker) {
    return [
        'period' => $faker->word,
        'paid_at' => Carbon::now(),
    ];
});
