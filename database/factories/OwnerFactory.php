<?php

use App\Models\Owner;
use App\Models\User;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(Owner::class, function (Faker $faker) {
    return [
        "submitted_at" => Carbon::now(),
        "approved_at" => Carbon::now(),
    ];
});

$factory->afterMaking(Owner::class, function ($owner) {
    if (!$owner->user_id) {
        $user = factory(User::class)
            ->states("withCommunity")
            ->create();
        $owner->user_id = $user->id;
    }
});
