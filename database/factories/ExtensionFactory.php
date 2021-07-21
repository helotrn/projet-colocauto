<?php

use App\Models\Extension;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Extension::class, function (Faker $faker) {
    return [
        'executed_at' => Carbon::now(),
        'status' => $faker->randomElement(['in_process', 'canceled', 'completed']),
        'new_duration' => $faker->numberBetween($min = 15, $max = 24*60),
        'comments_on_extension' => $faker->paragraph,
        'contested_at' => null,
        'comments_on_contestation' => null,
    ];
});
