<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends BaseModel
{
    use SoftDeletes;

    public static $filterTypes = [
        'name' => 'text',
        'type' => ['credit_card', 'bank_account'],
        'user_id' => User::class,
    ];

    public static $rules = [
        'name' => 'required',
        'external_id' => 'required',
        'type' => 'required',
        'four_last_digits' => [
          'digits:4',
          'nullable',
        ],
        'credit_card_type' => [
          'nullable',
          'string',
        ],
        'user_id' => [
          'required',
        ],
    ];

    public static function getRules($action = '', $auth = null) {
        $rules = parent::getRules($action, $auth);

        if ($auth->isAdmin()) {
            return $rules;
        }

        $rules['user_id'][] = "in:{$auth->id}";

        return $rules;
    }

    protected $fillable = [
        'credit_card_type',
        'external_id',
        'four_last_digits',
        'name',
        'type',
        'user_id',
    ];

    public $items = ['user'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function scopeAccessibleBy(Builder $query, $user) {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->whereUserId($user->id);
    }
}
