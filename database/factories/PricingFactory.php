<?php

use App\Models\Pricing;
use Faker\Generator as Faker;

$factory->define(Pricing::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'rule' => '10',
        'object_type' => $faker->randomElement([
            'App\Models\Bike' ,
            'App\Models\Car',
            'App\Models\Trailer',
        ]),
    ];
});
