<?php

namespace App\Models;

use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Expense extends BaseModel
{
    use SoftDeletes;

    public static $rules = [
        "name" => ["nullable"],
        "amount" => ["required","numeric","gt:0"],
        "type" => ["required", "in:credit,debit"],
        "executed_at" => ["nullable","date"],
    ];

    public static function getRules($action = "", $auth = null)
    {
        $rules = parent::getRules($action, $auth);
        if($action === "template") {
            $rules["amount"][1] = "decimal";
            unset($rules["amount"][2]);
        }
        return $rules;
    }

    protected $fillable = ["name", "amount", "type", "executed_at", "loanable_id", "user_id", "expense_tag_id"];

    public $items = ["user", "loanable", "tag"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loanable()
    {
        return $this->belongsTo(Loanable::class);
    }

    public function tag()
    {
        return $this->belongsTo(ExpenseTag::class, 'expense_tag_id');
    }

    public static function boot()
    {
        parent::boot();

        self::saved(function ($model) {
            if (!$model->executed_at) {
                // default date is today
                $model->executed_at = Carbon::now();
                $model->save();
            }
        });
    }
}
