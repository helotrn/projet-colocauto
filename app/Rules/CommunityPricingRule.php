<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CommunityPricingRule implements Rule
{
    public function passes($attribute, $value) {
        if (empty($value)) {
            return false;
        }

        foreach ($value as $pricing) {
            if ($pricing['object_type'] === null) {
                return true;
            }
        }

        if (count($value) < 3) {
            return false;
        }

        return true;
    }

    public function message() {
        return 'Spécifiez une tarification par défaut ou '
                    . 'une tarification pour tous les types.';
    }
}
