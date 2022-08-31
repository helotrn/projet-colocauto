<?php

namespace App\Models;

use App\Models\Community;
use App\Casts\ObjectTypeCast;
use App\Rules\PricingRule;
use Carbon\Carbon;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class Pricing extends BaseModel
{
    public static $language;

    /**
     * @return null|array|float
     *     null if line is invalid
     *     array of two floats for [trip_cost, insurance_cost]
     *     float for trip_cost only
     */
    public static function evaluateRuleLine($line, $data)
    {
        $language = static::getExpressionLanguage();

        // Skip:
        // - comments (lines starting with # preceded by any whitespace).
        // - empty lines or containing whitespace only
        if (preg_match("/^\s*#/", $line) || preg_match('/^\s*$/', $line)) {
            return null;
        }

        if (preg_match('/^SI\s+.+?\s+ALORS\s+.+$/', $line)) {
            $line = str_replace("SI", "", $line);
            $line = str_replace("ALORS", "?", $line);
            $line .= ": null";
        }

        $line = str_replace('$KM', "km", $line);
        $line = str_replace('$MINUTES', "minutes", $line);
        $line = str_replace('$OBJET', "loanable", $line);
        $line = str_replace('$EMPRUNT', "loan", $line);
        $line = str_replace(
            '$SURCOUT_ASSURANCE',
            "(loanable.type == 'car' " .
                "and (loan.start.year_eight_months_ago - loanable.year_of_circulation) <= 5)",
            $line
        );

        $line = str_replace(" NON ", " !", $line);
        $line = str_replace(" OU ", " or ", $line);
        $line = str_replace(" ET ", " and ", $line);

        $line = str_replace(" PAS DANS ", " not in ", $line);
        $line = str_replace(" DANS ", " in ", $line);

        $response = $language->evaluate($line, $data);

        $floorCost = function ($currency) {
            return floor($currency * 100.0) / 100;
        };

        if (is_array($response)) {
            if (count(array_filter($response, "is_numeric")) !== 2) {
                return null;
            }
            return array_map($floorCost, $response);
        } elseif (!is_numeric($response)) {
            return null;
        }

        return $floorCost($response);
    }

    public static function getExpressionLanguage()
    {
        if (self::$language) {
            return self::$language;
        }

        $language = new ExpressionLanguage();
        $language->register(
            "PLANCHER",
            function ($str) {
                return sprintf(
                    '(is_numeric(%1$s) ? intval(floor(%1$s)) : %1$s)',
                    $str
                );
            },
            function ($arguments, $str) {
                if (!is_numeric($str)) {
                    return $str;
                }

                return intval(floor($str));
            }
        );
        $language->register(
            "PLAFOND",
            function ($str) {
                return sprintf(
                    '(is_numeric(%1$s) ? intval(ceil(%1$s)) : %1$s)',
                    $str
                );
            },
            function ($arguments, $str) {
                if (!is_numeric($str)) {
                    return $str;
                }

                return intval(ceil($str));
            }
        );
        $language->register(
            "ARRONDI",
            function ($str) {
                return sprintf(
                    '(is_numeric(%1$s) ? intval(round(%1$s)) : %1$s)',
                    $str
                );
            },
            function ($arguments, $str) {
                if (!is_numeric($str)) {
                    return $str;
                }

                return intval(round($str));
            }
        );
        $language->register(
            "DOLLARS",
            function ($str) {
                return sprintf(
                    '(is_numeric(%1$s) ? intval(round(%1$s), 2) : %1$s)',
                    $str
                );
            },
            function ($arguments, $str) {
                if (!is_numeric($str)) {
                    return $str;
                }

                return number_format(round($str, 2), 2);
            }
        );

        self::$language = $language;

        return self::$language;
    }

    public static function dateToDataObject($date)
    {
        $date = new Carbon($date);
        return (object) [
            "year" => $date->year,
            "month" => $date->month,
            "day" => $date->day,
            "hour" => $date->hour,
            "minute" => $date->minute,
            "day_of_year" => $date->dayOfYear,
            "year_eight_months_ago" => $date->copy()->sub(8, "months")->year,
        ];
    }

    public static $rules = [
        "name" => "required",
        "object_type" => ["nullable"],
        "rule" => ["required"],
    ];

    public static function getRules($action = "", $auth = null)
    {
        $rules = static::$rules;
        $rules["rule"][] = new PricingRule();
        return $rules;
    }

    protected $casts = [
        "object_type" => ObjectTypeCast::class,
    ];

    protected $fillable = ["name", "object_type", "rule"];

    public $items = ["community"];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function evaluateRule($km, $minutes, $loanable = null, $loan = null)
    {
        $lines = explode("\n", $this->rule);

        if ($loanable instanceof Loanable) {
            $loanableData = $loanable->toArray();
        } else {
            $loanableData = $loanable;
        }

        if ($loan instanceof Loan) {
            $start = new Carbon($loan->departure_at);
            $end = $start
                ->copy()
                ->add($loan->actual_duration_in_minutes, "minutes");

            $loanData = [
                "days" => $loan->calendar_days,
                "start" => self::dateToDataObject($start),
                "end" => self::dateToDataObject($end),
            ];
        } else {
            $loanData = $loan;
        }

        foreach ($lines as $line) {
            try {
                $response = static::evaluateRuleLine($line, [
                    "km" => $km,
                    "minutes" => $minutes,
                    "loanable" => (object) $loanableData,
                    "loan" => (object) $loanData,
                ]);
                if ($response !== null) {
                    return $response;
                }
            } catch (SyntaxError $e) {
                // Should not happen but let it got at this point
            }
        }

        return null;
    }
}
