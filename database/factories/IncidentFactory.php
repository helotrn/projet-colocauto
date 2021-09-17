<?php

use App\Models\Incident;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Incident::class, function (Faker $faker) {
    return [
        "executed_at" => Carbon::now(),
        "status" => $faker->randomElement([
            "in_process",
            "canceled",
            "completed",
        ]),
        "incident_type" => $faker->randomElement([
            "accident",
            "small_incident",
        ]),
        "comments_on_incident" => $faker->sentence,
    ];
});
