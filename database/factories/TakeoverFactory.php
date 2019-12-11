<?php

use App\Models\Takeover;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Takeover::class, function (Faker $faker) {
    return [
        'executed_at' => $faker->dateTime($format = 'Y-m-d H:i:sO', $max = 'now'),
        'status' => $faker->randomElement(['in_process' ,'canceled', 'completed']),
        'mileage_beginning' => $faker->numberBetween($min = 0, $max = 300000),
        'fuel_beginning' => $faker->numberBetween($min = 0, $max = 100),
        'comments_on_vehicle' => $faker->sentence,
        'contested_at' => null,
        'comments_on_contestation' => null,
    ];
});
