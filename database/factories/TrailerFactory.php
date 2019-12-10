<?php

use App\Models\Trailer;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Trailer::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'position' => new Point($faker->latitude, $faker->longitude),
        'location_description' => $faker->sentence,
        'comments' => $faker->paragraph,
        'instructions' => $faker->paragraph,
        'type' => $faker->randomElement(['regular' ,'electric', 'fixed_wheel']),
        'maximum_charge' => $faker->numberBetween($min = 1000, $max = 9000),
    ];
});
