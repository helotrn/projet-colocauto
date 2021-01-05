<?php

use App\Models\Car;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Car::class, function (Faker $faker) {
    return [
        'availability_ics' => $faker->sentence,
        'brand' => $faker->word,
        'comments' => $faker->paragraph,
        'engine' => $faker->randomElement(['fuel' ,'diesel', 'electric', 'hybrid']),
        'has_informed_insurer' => $faker->boolean,
        'instructions' => $faker->paragraph,
        'insurer' => $faker->word,
        'is_value_over_fifty_thousand' => $faker->boolean,
        'location_description' => $faker->sentence,
        'model' => $faker->sentence,
        'name' => $faker->name,
        'owner_id' => 1,
        'papers_location' => $faker->randomElement(['in_the_car', 'to_request_with_car']),
        'plate_number' => $faker->shuffle('9F29J2'),
        'position' => [$faker->latitude, $faker->longitude],
        'pricing_category' => $faker->randomElement(['small', 'large']),
        'transmission_mode' => $faker->randomElement(['automatic' ,'manual']),
        'year_of_circulation' => $faker->year($max = 'now'),
    ];
});
