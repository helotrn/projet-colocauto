<?php

namespace App\Models;

use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Observers\RefundsObserver;
use App\Casts\TimestampWithTimezoneCast;

class Refund extends BaseModel
{
    use SoftDeletes;

    public static $rules = [
        "amount" => ["required","numeric","gte:0"],
        "executed_at" => ["nullable","date"],
        "user_id" => ["required","numeric"],
        "credited_user_id" => ["required","numeric","different:user_id"],
    ];

    public static function getRules($action = "", $auth = null)
    {
        $rules = parent::getRules($action, $auth);
        if($action === "template") {
            $rules["amount"][1] = "decimal";
            // remove greater than 0 rule for javascript
            unset($rules["amount"][2]);

            // remove different rule for Javascript
            unset($rules["credited_user_id"][2]);
        }
        return $rules;
    }

    public static $filterTypes = [
        "executed_at" => "date",
        "user.full_name" => "text",
        "credited_user.full_name" => "text",
        "user.communities.id" => [
            "type" => "relation",
            "query" => [
                "slug" => "communities",
                "value" => "id",
                "text" => "name",
                "params" => [
                    "fields" => "id,name"
                ]
            ]
        ],
    ];

    protected $fillable = ["amount", "executed_at", "user_id", "credited_user_id"];

    public $items = ["user", "credited_user"];

    public $collections = ["changes"];

    public $computed = ["community"];

    protected $casts = [
        "executed_at" => TimestampWithTimezoneCast::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creditedUser()
    {
        return $this->belongsTo(User::class, 'credited_user_id');
    }

    public function changes()
    {
        return $this->hasMany(RefundChange::class);
    }

    public static function boot()
    {
        parent::boot();
        
        Refund::observe(new RefundsObserver);

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

        if ($user->isCommunityAdmin()) {
            return $query->whereHas("user", function ($q) use ($user) {
                return $q->accessibleBy($user);
            })
            ->orWhereHas("creditedUser", function ($q) use ($user) {
                return $q->accessibleBy($user);
            });
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

    public function getCommunityAttribute()
    {
        return $this->user->communities->first();
    }
}
