<?php

use App\Models\Car;
use Illuminate\Support\Str;
use Faker\Generator as Faker;
use Phaza\LaravelPostgis\Geometries\Point;

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
        'engine' => $faker->randomElement(['fuel' ,'diesel', 'electric', 'hybrid']),
        'plate_number' => $faker->shuffle('9F29J2'),
        'is_value_over_fifty_thousand' => $faker->boolean,
        'ownership' => $faker->randomElement(['self', 'rental']),
        'papers_location' => $faker->randomElement(['in_the_car', 'to_request_with_car']),
        'has_accident_report' => $faker->boolean,
        'insurer' => $faker->word,
        'has_informed_insurer' => $faker->boolean,
        'availability_ics' => $faker->sentence,
        'owner_id' => 1,
    ];
});
