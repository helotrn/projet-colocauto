<?php

namespace App\Models;

use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends BaseModel
{
    use SoftDeletes;

    public function rules()
    {
        return [
            "name" => ["required"],
            "amount" => ["required","numeric","gt:0"],
            "type" => ["required", Rule::in('credit','debit')],
            "executed_at" => ["nullable","date"], // TODO: use today date if not provided
        ];
    }

    protected $fillable = ["name", "amount", "type", "executed_at", "loanable_id", "user_id"];

    public $items = ["user", "loanable"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loanable()
    {
        return $this->belongsTo(Loanable::class);
    }
}
