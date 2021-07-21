<?php

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Invoice::class, function (Faker $faker) {
    return [
        'period' => \Carbon\Carbon::now()->locale('fr_FR')->isoFormat('LLLL'),
        'paid_at' => Carbon::now(),
    ];
});
