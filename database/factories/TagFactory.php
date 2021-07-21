<?php

use App\Models\Tag;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Tag::class, function (Faker $faker) {
    return [
        "name" => $faker->name,
        "slug" => $faker->name,
        "type" => $faker->randomElement(["tag"]),
    ];
});
