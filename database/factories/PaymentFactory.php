<?php

use App\Models\Payment;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(App\Payment::class, function (Faker $faker) {
    return [
        'status' => $faker->randomElement(['in_process', 'canceled', 'completed']),
    ];
});
