<?php

use App\Models\BillableItem;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(App\BillableItem::class, function (Faker $faker) {
    return [
        'label' => $faker->word,
        'amount' => $faker->numberBetween($min = 0, $max = 300000),
    ];
});
