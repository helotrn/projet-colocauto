<?php

namespace App\Models;

use Illuminate\Validation\Rule;
use Carbon\Carbon;

class RefundChange extends BaseModel
{
    protected $fillable = ["refund_id", "user_id", "description"];

    public $items = ["refund", "user"];

    public function refund()
    {
        return $this->belongsTo(Refund::class);
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
            ->whereHas("refund", function($q) use ($user) {
                return $q->accessibleBy($user);
            });
    }
}
