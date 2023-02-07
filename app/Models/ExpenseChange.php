<?php

namespace App\Models;

use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ExpenseChange extends BaseModel
{
    protected $fillable = ["expense_id", "user_id", "description"];

    public $items = ["expense", "user"];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAccessibleBy(Builder $query, $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query
            ->whereHas("expense", function($q) use ($user) {
                return $q->accessibleBy($user);
            });
    }
}
