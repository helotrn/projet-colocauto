<?php

namespace App\Providers;

use App\Models\Loanable;
use Illuminate\Support\ServiceProvider;
use Validator;

class CustomValidationServiceProvider extends ServiceProvider
{
    public function boot() {
        Validator::extend('available', function ($attribute, $value, $parameters, $validator) {
            [
                'departure_at' => $departureAt,
                'duration_in_minutes' => $durationInMinutes
            ] = $validator->getData();
            return Loanable::find($value)->isAvailable($departureAt, $durationInMinutes);
        });

        Validator::replacer('available', function () {
            return "Le véhicule n'est pas disponible sur cette période.";
        });
    }
}
