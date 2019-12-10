<?php

use App\Models\Padlock;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Padlock::class, function (Faker $faker) {
    return [
        'mac_address' => $faker->macAddress,
    ];
});
