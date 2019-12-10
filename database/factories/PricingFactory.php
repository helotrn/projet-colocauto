<?php

use App\Models\Pricing;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Pricing::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'object_type' => $faker->sentence,
        'variable' => $faker->randomElement(['time' ,'distance']),
        'rule' => $faker->sentence,
    ];
});
