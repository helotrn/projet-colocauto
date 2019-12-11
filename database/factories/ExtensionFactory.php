<?php

use App\Models\Extension;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Extension::class, function (Faker $faker) {
    return [
        'executed_at' => $faker->dateTime($format = 'Y-m-d H:i:sO', $max = 'now'),
        'status' => $faker->randomElement(['in_process', 'canceled', 'completed']),
        'new_duration' => $faker->randomNumber($nbDigits = null, $strict = false),
        'comments_on_extension' => $faker->paragraph,
        'contested_at' => null,
        'commments_on_contestation' => null,
    ];
});
