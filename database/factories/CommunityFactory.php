<?php

use App\Models\Community;
use App\Models\Pricing;
use Faker\Generator as Faker;

$factory->define(Community::class, function (Faker $faker) {
    return [
        "name" => $faker->name,
        "description" => $faker->sentence,
        "area" => null,
    ];
});

$factory->afterCreatingState(
    Community::class,
    "withDefaultFreePricing",
    function (Community $community) {
        factory(Pricing::class)->make([
            "object_type" => null,
            "rule" => "0",
            "community_id" => $community->id,
        ]);
    }
);

$factory->afterCreatingState(
    Community::class,
    "withDefault10DollarsPricing",
    function (Community $community) {
        factory(Pricing::class)->create([
            "object_type" => null,
            "rule" => "[10, 0]",
            "community_id" => $community->id,
        ]);
    }
);
