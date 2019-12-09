<?php

use App\Models\Intention;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(App\Intention::class, function (Faker $faker) {
    return [
        'status' => $faker->randomElement(['in_process', 'canceled', 'completed']),
    ];
});
