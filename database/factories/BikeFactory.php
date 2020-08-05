<?php

use App\Models\Bike;
use Illuminate\Support\Str;
use Faker\Generator as Faker;
use Phaza\LaravelPostgis\Geometries\Point;

$factory->define(Bike::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'position' => new Point($faker->latitude, $faker->longitude),
        'location_description' => $faker->sentence,
        'comments' => $faker->paragraph,
        'instructions' => $faker->paragraph,
        'model' => $faker->sentence,
        'bike_type' => $faker->randomElement(['regular', 'cargo','electric', 'fixed_wheel']),
        'size' => $faker->randomElement(['big' ,'medium', 'small', 'kid']),
        'availability_mode' => 'always',
        'owner_id' => 1,
    ];
});
