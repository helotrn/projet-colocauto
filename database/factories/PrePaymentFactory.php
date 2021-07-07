<?php

use App\Models\PrePayment;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(PrePayment::class, function (Faker $faker) {
    return [
        'executed_at' => Carbon::now(),
        'status' => $faker->randomElement(['in_process', 'canceled', 'completed']),
    ];
});
