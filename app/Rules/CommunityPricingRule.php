<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CommunityPricingRule implements Rule
{
    public function passes($attribute, $value)
    {
        if (empty($value)) {
            return false;
        }

        $pricingsByType = array_reduce(
            $value,
            function ($acc, $p) {
                if ($p["object_type"] === null) {
                    $type = "null";
                } else {
                    $type = $p["object_type"];
                }

                if (!isset($acc[$type])) {
                    $acc[$type] = 0;
                }

                $acc[$type]++;

                return $acc;
            },
            []
        );

        if (count($value) < 3 && !isset($pricingsByType["null"])) {
            return false;
        }

        foreach ($pricingsByType as $type) {
            if ($type > 1) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return "Spécifiez une tarification par défaut ou " .
            "une tarification pour tous les autres types.";
    }
}
