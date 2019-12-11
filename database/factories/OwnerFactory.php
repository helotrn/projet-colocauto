<?php

use App\Models\Owner;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Owner::class, function (Faker $faker) {
    return [
        'submitted_at' => $faker->date($format = 'Y-m-d', $max = 'now'),
        'approved_at' => $faker->date($format = 'Y-m-d', $max = 'now'),
    ];
});
