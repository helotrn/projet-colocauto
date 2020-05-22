<?php

use App\Models\PaymentMethod;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(PaymentMethod::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'external_id' => $faker->sentence,
        'type' => $faker->randomElement(['bank_account']),
        'four_last_digits' => $faker->randomNumber($nbDigits = 4, $strict = true),
        'credit_card_type' => $faker->creditCardType,
    ];
});
