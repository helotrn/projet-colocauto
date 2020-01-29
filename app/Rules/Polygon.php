<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Polygon implements Rule
{
    public function passes($attribute, $value) {
        return $value[0] === $value[count($value) - 1];
    }

    public function message() {
        return 'Le polygone est invalide. Le premier et le dernier point doivent être identiques.';
    }
}
