<?php

use App\Models\Padlock;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Padlock::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'mac_address' => $faker->macAddress,
        'external_id' => $faker->asciify('*********'),
    ];
});
