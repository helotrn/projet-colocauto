<?php

namespace App\Rules;

use App\Models\Pricing;
use Illuminate\Contracts\Validation\Rule;

class PricingRule implements Rule
{
    protected $message;

    public function passes($attribute, $value)
    {
        $lines = explode("\n", $value);

        foreach ($lines as $index => $line) {
            try {
                Pricing::evaluateRuleLine($line, [
                    "km" => 1,
                    "minutes" => 1,
                    "loanable" => (object) [
                        "pricing_category" => "large",
                        "year_of_circulation" => 1999,
                    ],
                    "loan" => (object) [
                        "days" => 1,
                        "start" => Pricing::dateToDataObject("now"),
                        "end" => Pricing::dateToDataObject("+ 1 hour"),
                    ],
                ]);
            } catch (\Exception $e) {
                $this->message = $this->rebuildMessage($e->getMessage());
                return false;
            }
        }

        if (preg_match("/SI|ALORS/", $lines[count($lines) - 1])) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return $this->message ?: "The :attribute is invalid.";
    }

    private function rebuildMessage($message)
    {
        $message = str_replace("km", '$KM', $message);
        $message = str_replace("minutes", '$MINUTES', $message);
        $message = str_replace("loanable", '$OBJET', $message);
        $message = str_replace("loan", '$EMPRUNT', $message);

        $message = str_replace(" !", " NON ", $message);
        $message = str_replace(" or ", " OU ", $message);
        $message = str_replace(" and ", " ET ", $message);

        $message = str_replace(" not in ", " PAS DANS ", $message);
        $message = str_replace(" in ", " DANS ", $message);

        return $message;
    }
}
