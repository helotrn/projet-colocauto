<?php

use App\Models\Borrower;
use App\Models\User;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(User::class, function (Faker $faker) {
    return [
        "accept_conditions" => true,
        "name" => $faker->name,
        "last_name" => "",
        "email" => $faker->unique()->safeEmail,
        "email_verified_at" => Carbon::now(),
        "password" =>
            '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        "description" => null,
        "date_of_birth" => null,
        "address" => "",
        "postal_code" => "",
        "phone" => "",
        "is_smart_phone" => false,
        "other_phone" => "",
        "remember_token" => Str::random(10),
    ];
});

$factory->afterCreatingState(User::class, "withBorrower", function (
    $user,
    $faker
) {
    $user->borrower()->save(factory(Borrower::class)->make());
});
