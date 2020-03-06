<?php

namespace App\Models;

use App\Models\Community;
use App\Utils\ObjectTypeCast;
use App\Rules\PricingRule;
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

    public function evaluateRule($km, $minutes, $loanable = null) {
        $lines = explode("\n", $this->rule);

        foreach ($lines as $line) {
            if ($response = static::evaluateRuleLine($line, [
                'km' => $km,
                'minutes' => $minutes,
                'loanable' => (object) $loanable,
            ])) {
                return $response;
            }
        }

        return null;
    }
}
