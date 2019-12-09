<?php

use App\Models\Extension;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(App\Extension::class, function (Faker $faker) {
    return [
        'status' => $faker->randomElement(['in_process', 'canceled', 'completed']),
        'new_duration' => $faker->randomNumber($nbDigits = null, $strict = false),
        'comments_on_extension' => $faker->paragraph,
    ];
});
