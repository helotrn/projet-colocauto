<?php

use App\Models\Expense;
use App\Models\Bike;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Expense::class, function (Faker $faker) {
    return [
        "name" => $faker->text,
        "amount" => $faker->randomNumber($nbDigits = 4)/100,
    ];
});

$factory->afterMaking(Expense::class, function ($expense) {
    if (!$expense->user_id) {
        $user = factory(User::class)
            ->states("withCommunity")
            ->create();
        $expense->user_id = $user->id;
    }
    if (!$expense->loanable_id) {
        $loanable = factory(Bike::class)->create();
        $loanable->community_id = $user->communities[0]->id;
        $loanable->save();
        $expense->loanable_id = $loanable->id;
    }
});
