<?php

use App\Models\Owner;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Owner::class, function (Faker $faker) {
    return [
        "submitted_at" => Carbon::now(),
        "approved_at" => Carbon::now(),
    ];
});
