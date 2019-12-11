<?php

use App\Models\Payment;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Payment::class, function (Faker $faker) {
    return [
        'executed_at' => $faker->dateTime($format = 'Y-m-d H:i:sO', $max = 'now'),
        'status' => $faker->randomElement(['in_process', 'canceled', 'completed']),
    ];
});
