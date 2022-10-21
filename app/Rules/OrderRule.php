<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class OrderRule implements Rule
{
    public function passes($attribute, $value)
    {
        if (empty($value)) {
            return false;
        }

        $fields = explode(",", $value);

        foreach ($fields as $field) {
            if (
                !preg_match("/^-?[a-zA-Z0-9_]+[a-zA-Z0-9_.]*$/", trim($field))
            ) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return "The order parameter is invalid.";
    }
}
