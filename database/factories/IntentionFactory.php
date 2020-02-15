<?php

use App\Models\Intention;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Intention::class, function (Faker $faker) {
    return [
        'executed_at' => Carbon::now(),
        'status' => $faker->randomElement(['in_process', 'canceled', 'completed']),
    ];
});
