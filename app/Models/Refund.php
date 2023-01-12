<?php

namespace App\Models;

use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Refund extends BaseModel
{
    use SoftDeletes;

    public static $rules = [
        "amount" => ["required","numeric","gt:0"],
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

    protected $fillable = ["amount", "executed_at", "user_id", "credited_user_id"];

    public $items = ["user", "credited_user"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creditedUser()
    {
        return $this->belongsTo(User::class, 'credited_user_id');
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

    public function scopeAccessibleBy(Builder $query, $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        // A user has access to...
        return $query
            // ... refunds payed by himself or herself
            ->where('user_id', $user->id)
            // ...or refunds payed by somebody belonging to the same community
            ->orWhereIn('user_id', $user->getSameCommunityUserIds())
            // ... or refunds payed to himself or herself
            ->orWhere('credited_user_id', $user->id)
            // ...or refunds payed to somebody belonging to the same community
            ->orWhereIn('credited_user_id', $user->getSameCommunityUserIds());
    }
}
