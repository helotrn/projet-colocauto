<?php

namespace App\Models;

use App\Models\Community;
use App\Utils\ObjectTypeCast;
use App\Rules\PricingRule;
use Carbon\Carbon;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Vkovic\LaravelCustomCasts\HasCustomCasts;

class Pricing extends BaseModel
{
    use HasCustomCasts;

    public static $language;

    public static function evaluateRuleLine($line, $data) {
        $language = static::getExpressionLanguage();

        if (preg_match('/^SI\s+.+?\s+ALORS\s+.+$/', $line)) {
            $line = str_replace('SI', '', $line);
            $line = str_replace('ALORS', '?', $line);
            $line .= ': null';
        }

        $line = str_replace('$KM', 'km', $line);
        $line = str_replace('$MINUTES', 'minutes', $line);
        $line = str_replace('$OBJET', 'loanable', $line);
        $line = str_replace('$EMPRUNT', 'loan', $line);

        $response = $language->evaluate($line, $data);

        if (is_array($response)) {
            if (count(array_filter($response, 'is_numeric')) !== 2) {
                return null;
            }
        } elseif (!is_numeric($response)) {
            return null;
        }

        return $response;
    }

    public static function getExpressionLanguage() {
        if (self::$language) {
            return self::$language;
        }

        $language = new ExpressionLanguage();
        $language->register('PLANCHER', function ($str) {
            return sprintf('(is_numeric(%1$s) ? intval(floor(%1$s)) : %1$s)', $str);
        }, function ($arguments, $str) {
            if (!is_numeric($str)) {
                return $str;
            }

            return intval(floor($str));
        });
        $language->register('PLAFOND', function ($str) {
            return sprintf('(is_numeric(%1$s) ? intval(ceil(%1$s)) : %1$s)', $str);
        }, function ($arguments, $str) {
            if (!is_numeric($str)) {
                return $str;
            }

            return intval(ceil($str));
        });
        $language->register('ARRONDI', function ($str) {
            return sprintf('(is_numeric(%1$s) ? intval(round(%1$s)) : %1$s)', $str);
        }, function ($arguments, $str) {
            if (!is_numeric($str)) {
                return $str;
            }

            return intval(round($str));
        });
        $language->register('DOLLARS', function ($str) {
            return sprintf('(is_numeric(%1$s) ? intval(round(%1$s), 2) : %1$s)', $str);
        }, function ($arguments, $str) {
            if (!is_numeric($str)) {
                return $str;
            }

            return number_format(round($str, 2), 2);
        });

        self::$language = $language;

        return self::$language;
    }

    public static function dateToDataArray($date) {
        $date = new Carbon($date);
        return [
            'year' => $date->year,
            'month' => $date->month,
            'day' => $date->day,
            'hour' => $date->hour,
            'minute' => $date->minute,
            'day_of_year' => $date->dayOfYear,
        ];
    }

    public static $rules = [
        'name' => 'required',
        'object_type' => [
          'nullable',
        ],
        'rule' => ['required'],
    ];

    public static function getRules($action = '', $auth = null) {
        $rules = static::$rules;
        $rules['rule'][] = new PricingRule;
        return $rules;
    }

    protected $casts = [
        'object_type' => ObjectTypeCast::class,
    ];

    protected $fillable = [
        'name',
        'object_type',
        'rule',
    ];

    public $items = ['community'];

    public function community() {
        return $this->belongsTo(Community::class);
    }

    public function evaluateRule($km, $minutes, $loanable = null, $loan = null) {
        $lines = explode("\n", $this->rule);

        if ($loanable instanceof Loanable) {
            $loanableData = $loanable->toArray();
        } else {
            $loanableData = $loanable;
        }

        if ($loan instanceof Loan) {
            $start = new Carbon($loan->departure_at);
            $end = $start->copy()->add($loan->actual_duration_in_minutes, 'minutes');

            $loanData = [
                'days' => $loan->calendar_days,
                'start' =>  self::dateToDataArray($start),
                'end' => self::dateToDataArray($end)
            ];
        } else {
            $loanData = $loan;
        }

        foreach ($lines as $line) {
            try {
                $response = static::evaluateRuleLine($line, [
                    'km' => $km,
                    'minutes' => $minutes,
                    'loanable' => (object) $loanableData,
                    'loan' => (object) $loanData,
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
