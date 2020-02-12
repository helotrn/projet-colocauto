<?php

use App\Models\Intention;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Intention::class, function (Faker $faker) {
    return [
        'executed_at' => now(),
        'status' => $faker->randomElement(['in_process', 'canceled', 'completed']),
    ];
});
