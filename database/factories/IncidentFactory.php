<?php

use App\Models\Incident;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Incident::class, function (Faker $faker) {
    return [
        'status' => $faker->randomElement(['in_process', 'canceled', 'completed']),
        'incident_type' => $faker->randomElement(['accident']),
    ];
});
