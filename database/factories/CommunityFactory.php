<?php

use App\Models\Community;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Community::class, function (Faker $faker) {
    return [
        "name" => $faker->name,
        "description" => $faker->sentence,
        "area" => null,
    ];
});
