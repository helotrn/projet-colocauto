<?php

namespace App\Providers;

use App\Models\Loan;
use App\Models\Loanable;
use Illuminate\Support\ServiceProvider;
use Validator;

class CustomValidationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Validator::extend("available", function (
            $attribute,
            $value,
            $parameters,
            $validator
        ) {
            $validatorData = $validator->getData();
            [
                "departure_at" => $departureAt,
                "duration_in_minutes" => $durationInMinutes,
            ] = $validatorData;

            $ignoreLoanIds = [];
            if (isset($validatorData["id"])) {
                $ignoreLoanIds = [$validatorData["id"]];
            }

            return Loanable::find($value)->isAvailable(
                $departureAt,
                $durationInMinutes,
                $ignoreLoanIds
            );
        });

        Validator::replacer("available", function () {
            return "Le véhicule n'est pas disponible sur cette période.";
        });

        Validator::extend("extendable", function (
            $attribute,
            $value,
            $parameters,
            $validator
        ) {
            [
                "new_duration" => $newDuration,
            ] = $validator->getData();
            $loan = Loan::find($value);
            return $loan->loanable->isAvailable(
                $loan->departure_at,
                $newDuration,
                [$loan->id]
            );
        });

        Validator::replacer("extendable", function () {
            return "Le véhicule n'est pas disponible sur cette période.";
        });
    }
}
