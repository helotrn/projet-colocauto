<?php

use App\Models\Report;
use App\Models\Car;
use Faker\Generator as Faker;

$factory->define(Report::class, function (Faker $faker) {
    $loanable = factory(Car::class)->create();
    return [
        "location" => $faker->randomElement(['front', 'back', 'right', 'left', 'inside']),
        "details" => $faker->paragraph,
        "loanable_id" => $loanable->id,
    ];
});
