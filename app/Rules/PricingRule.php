<?php

namespace App\Rules;

use App\Models\Pricing;
use Illuminate\Contracts\Validation\Rule;

class PricingRule implements Rule
{
    public function passes($attribute, $value) {
        $lines = explode("\n", $value);

        foreach ($lines as $index => $line) {
            try {
                Pricing::evaluateRuleLine($line, [
                    'km' => 1,
                    'minutes' => 1,
                    'object' => [
                        'pricing_category' => 'large',
                    ],
                    'loan' => [
                        'days' => 1,
                        'start' => Pricing::dateToDataArray('now'),
                        'end' => Pricing::dateToDataArray('+ 1 hour'),
                    ],
                ]);
            } catch (\Exception $e) {
                return false;
            }
        }

        if (preg_match('/SI|ALORS/', $lines[count($lines) - 1])) {
            return false;
        }

        return true;
    }

    public function message() {
        return 'The :attribute is invalid.';
    }
}
