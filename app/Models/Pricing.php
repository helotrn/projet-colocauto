<?php

namespace App\Models;

use App\Models\Community;
use App\Utils\ObjectTypeCast;
use App\Rules\PricingRule;
use Carbon\Carbon;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Vkovic\LaravelCustomCasts\HasCustomCasts;

class Pricing extends BaseModel
{
    use HasCustomCasts;

    public static function evaluateRuleLine($line, $data) {
        $expressionLanguage = new ExpressionLanguage();

        if (preg_match('/^SI\s+.+?\s+ALORS\s+.+$/', $line)) {
            $line = str_replace('SI', '', $line);
            $line = str_replace('ALORS', '?', $line);
            $line .= ': null';
        }

        $line = str_replace('$KM', 'km', $line);
        $line = str_replace('$MINUTES', 'minutes', $line);
        $line = str_replace('$OBJET', 'loanable', $line);
        $line = str_replace('$EMPRUNT', 'loan', $line);

        return $expressionLanguage->evaluate($line, $data);
    }

    public static function buildGenericObject($type = null) {
        $object = new \stdClass;

        switch ($type) {
            case 'car':
                $object->year_of_circulation = 1;
                break;
            default:
                break;
        }

        return $object;
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

        if ($loan instanceof Loan) {
            $start = new Carbon($loan->departure_at);
            $end = $start->add($loan->actual_duration_in_minutes, 'minutes');

            $loanData = [
                'days' => $end->diffInDays($start),
                'start' =>  [
                    'year' => $start->year,
                    'month' => $start->month,
                    'day' => $start->day,
                    'hour' => $start->hour,
                    'minute' => $start->minute,
                ],
                'end' => [
                    'year' => $end->year,
                    'month' => $end->month,
                    'day' => $end->day,
                    'hour' => $end->hour,
                    'minute' => $end->minute,
                ]
            ];
        } else {
            $loanData = (object) $loan;
        }

        foreach ($lines as $line) {
            $response = static::evaluateRuleLine($line, [
                'km' => $km,
                'minutes' => $minutes,
                'loanable' => (object) $loanable,
                'loan' => $loanData,
            ]);
            if ($response !== null) {
                return $response;
            }
        }

        return null;
    }
}
