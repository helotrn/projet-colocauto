<?php

use App\Models\Invitation;
use App\Models\Community;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Invitation::class, function (Faker $faker) {
    return [
        "email" => $faker->email,
        "token" => $faker->regexify('[a-zA-Z]{20}'),
    ];
});

$factory->afterCreatingState(Invitation::class, "withCommunity", function ($invitation) {
    $community = factory(Community::class)->create();
    $invitation->community_id = $community->id;
    $invitation->save();
});
