<?php

namespace App\Helpers;

class Order
{
    /*
      @param orderParam:
        String representing the order parameter as normally accepted in API requests.
        Eg.: -name,type,-departure_at

      @param fieldDefs:
        Optional field definitions. Useful to pass data type or eventually a
        compare function for sorting.
    */
    public static function parseOrderRequestParam($orderParam, $fieldDefs = [])
    {
        if (!$orderParam) {
            return [];
        }

        $fieldOrderStrings = explode(",", $orderParam);
        $orderArray = [];
        foreach ($fieldOrderStrings as $fieldOrderString) {
            $fieldOrder = [];

            $fieldOrderString = trim($fieldOrderString);

            // Field name and direction
            if (strpos($fieldOrderString, "-") === 0) {
                $fieldOrder = [
                    "field" => ($fieldOrderString = substr(
                        $fieldOrderString,
                        1
                    )),
                    "direction" => "desc",
                ];
            } else {
                $fieldOrder = [
                    "field" => $fieldOrderString,
                    "direction" => "asc",
                ];
            }

            // Sort spec.
            if (isset($fieldDefs[$fieldOrderString]["type"])) {
                $fieldOrder["type"] = $fieldDefs[$fieldOrderString]["type"];
            }

            $orderArray[] = $fieldOrder;
        }

        return $orderArray;
    }

    /*
       Sort an array according to orderArray.

       @param orderArray:
          Array containing field order parameters for each field:
            field:
              The name of the field.
            direction:
              Sort direction.
            type:
              Type of field, so as to select the appropriate comparison function.
    */
    public static function sortArray(&$array, $orderArray)
    {
        usort($array, function ($a, $b) use ($orderArray) {
            $cmp = 0;

            foreach ($orderArray as $fieldOrder) {
                $fieldType = isset($fieldOrder["type"])
                    ? $fieldOrder["type"]
                    : "";
                $fieldName = $fieldOrder["field"];

                switch ($fieldType) {
                    case "string":
                    default:
                        $cmp = self::compareStrings(
                            $a[$fieldName],
                            $b[$fieldName]
                        );
                        break;

                    case "carbon":
                        $cmp = self::compareCarbon(
                            $a[$fieldName],
                            $b[$fieldName]
                        );
                        break;
                }

                // Account for direction.
                if (0 !== $cmp) {
                    if ("desc" == $fieldOrder["direction"]) {
                        $cmp = -$cmp;
                    }
                    break;
                }
            }

            return $cmp;
        });

        return $array;
    }

    /*
      Compare functions are expected to return > 0 if $a > $b, and < 0 if $a < $b.
      Returning $a - $b, for numbers respects this assumption.
    */
    public static function compareStrings($a, $b)
    {
        return strcmp($a, $b);
    }

    public static function compareCarbon($a, $b)
    {
        if ($a->lessThan($b)) {
            return -1;
        } elseif ($a->greaterThan($b)) {
            return 1;
        }
        return 0;
    }
}
