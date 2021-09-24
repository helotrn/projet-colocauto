<?php

use App\Models\Borrower;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Borrower::class, function (Faker $faker) {
    return [
        "drivers_license_number" => $faker->numberBetween(
            $min = 1111111111,
            $max = 999999999
        ),
        "has_been_sued_last_ten_years" => $faker->boolean,
        "noke_id" => $faker->numberBetween($min = 000000000, $max = 999999999),
        "submitted_at" => date("Y-m-d"),
        "approved_at" => null,
    ];
});

$factory->afterMaking(Borrower::class, function ($borrower) {
    if (!$borrower->user_id) {
        $user = factory(User::class)->create();
        $borrower->user_id = $user->id;
    }
});
