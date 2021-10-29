<?php

use App\Models\Bike;
use App\Models\Community;
use App\Models\Owner;
use App\Models\Padlock;
use Faker\Generator as Faker;

$factory->define(Bike::class, function (Faker $faker) {
    return [
        "name" => $faker->name,
        "position" => [$faker->latitude, $faker->longitude],
        "location_description" => $faker->sentence,
        "comments" => $faker->paragraph,
        "instructions" => $faker->paragraph,
        "model" => $faker->sentence,
        "bike_type" => $faker->randomElement([
            "regular",
            "cargo",
            "electric",
            "fixed_wheel",
        ]),
        "size" => $faker->randomElement(["big", "medium", "small", "kid"]),
        "availability_mode" => "always",
    ];
});

$factory->afterMaking(Bike::class, function ($bike) {
    if (!$bike->owner_id) {
        $owner = factory(Owner::class)->create();
        $bike->owner_id = $bike->id;
    }
});

$factory->afterCreatingState(Bike::class, "withCommunity", function ($bike) {
    $community = factory(Community::class)
        ->states("withDefaultFreePricing")
        ->create();
    $bike->community_id = $community->id;
    $bike->save();
});

$factory->afterCreatingState(Bike::class, "withPadlock", function ($bike) {
    factory(Padlock::class)->create([
        "loanable_id" => $bike->id,
    ]);
});
