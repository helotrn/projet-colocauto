<?php

use App\Models\Takeover;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Takeover::class, function (Faker $faker) {
    return [
        'executed_at' => Carbon::now(),
        'status' => $faker->randomElement(['in_process' ,'canceled', 'completed']),
        'mileage_beginning' => $faker->numberBetween($min = 0, $max = 300000),
        'comments_on_vehicle' => $faker->sentence,
        'contested_at' => null,
        'comments_on_contestation' => null,
    ];
});
