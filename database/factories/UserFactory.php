<?php

use App\Models\Borrower;
use App\Models\Community;
use App\Models\Owner;
use App\Models\User;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(User::class, function (Faker $faker) {
    return [
        "accept_conditions" => true,
        "name" => $faker->firstName,
        "last_name" => "",
        "email" => $faker->unique()->safeEmail,
        "email_verified_at" => Carbon::now(),
        "password" =>
            '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        "description" => null,
        "date_of_birth" => null,
        "address" => $faker->address,
        "postal_code" => $faker->postCode,
        "phone" => "",
        "is_smart_phone" => false,
        "other_phone" => "",
        "remember_token" => Str::random(10),
        "transaction_id" => 0,
    ];
});

$factory->afterCreatingState(User::class, "withBorrower", function (
    $user,
    $faker
) {
    $user->borrower()->save(factory(Borrower::class)->make());
});

$factory->afterCreatingState(User::class, "withOwner", function (
    $user,
    $faker
) {
    $user->owner()->save(factory(Owner::class)->make());
});

$factory->afterCreatingState(User::class, "withCommunity", function (
    User $user,
    Faker $faker
) {
    $user->communities()->save(factory(Community::class)->make());
});

$factory->afterCreatingState(User::class, "withPaidCommunity", function (
    User $user,
    Faker $faker
) {
    $user->communities()->save(
        factory(Community::class)
            ->states("withDefault10DollarsPricing")
            ->create()
    );
});
