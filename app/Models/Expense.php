<?php

namespace App\Models;

use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Expense extends BaseModel
{
    use SoftDeletes;

    public function rules()
    {
        return [
            "name" => ["required"],
            "amount" => ["required","numeric","gt:0"],
            "type" => ["required", Rule::in('credit','debit')],
            "executed_at" => ["nullable","date"],
        ];
    }

    protected $fillable = ["name", "amount", "type", "executed_at", "loanable_id", "user_id"];

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
        return $this->belongsTo(ExpenseTag::class);
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
