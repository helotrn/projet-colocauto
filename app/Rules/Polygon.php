<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/*
  This rule only accepts what will result in a valid polygon.

  This rule is to analyze in parallel with PolygonCast.
*/
class Polygon implements Rule
{
    public function passes($attribute, $value) {
        if (is_array($value)) {
                             // Accept an empty array.
            if (empty($value)) {
                return true;
            }

            $nPoints = count($value);

                             // Accept a degenerate polygon with a
                             // single (closed) point.
            if ($nPoints < 2) {
                return false;
            }

                             // First and last point must be
                             // identical (closed polygon).
            return $value[0] === $value[$nPoints - 1];
        }

                             // Accept nulls
        if (null === $value) {
            return true;
        }

        return false;
    }

    public function message() {
        return
            'Le polygone est invalide. Il doit être constitué d\'au moins'
          . ' deux points, dont le premier et le dernier doivent être identiques.';
    }
}
