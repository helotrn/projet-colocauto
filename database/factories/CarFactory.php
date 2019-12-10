<?php

use App\Models\Car;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Car::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'position' => new Point($faker->latitude, $faker->longitude),
        'location_description' => $faker->sentence,
        'comments' => $faker->paragraph,
        'instructions' => $faker->paragraph,
        'brand' => $faker->word,
        'model' => $faker->sentence,
        'year_of_circulation' => $faker->year($max = 'now'),
        'transmission_mode' => $faker->randomElement(['automatic' ,'manual']),
        'fuel' => $faker->randomElement(['fuel' ,'diesel', 'electric', 'hybrid']),
        'plate_number' => $faker->shuffle('9F29J2'),
        'is_value_over_fifty_thousand' => $faker->boolean,
        'owners' => $faker->randomElement(['yourself', 'dealership']),
        'papers_location' => $faker->randomElement(['in_the_car', 'to_request_with_car']),
        'has_accident_report' => $faker->boolean,
        'insurer' => $faker->word,
        'has_informed_insurer' => $faker->boolean,
    ];
});
