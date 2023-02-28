<?php

namespace App\Models;

use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Observers\ExpensesObserver;

class Expense extends BaseModel
{
    use SoftDeletes;

    public static $rules = [
        "name" => ["nullable"],
        "amount" => ["required","numeric","gt:0"],
        "type" => ["nullable", "in:credit,debit"],
        "executed_at" => ["nullable","date"],
        "locked" => ["boolean"],
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

    public static $filterTypes = [
        "id" => "number",
        "executed_at" => "date",
        "name" => "text",
        "type" => ["debit","credit"],
        "user.full_name" => "text",
        "loanable.name" => "text",
        "tag.id" => [
            "type" => "relation",
            "query" => [
                "slug" => "expense_tags",
                "value" => "id",
                "text" => "name",
                "params" => [
                    "fields" => "id,name"
                ]
            ]
        ]
    ];

    protected $fillable = ["name", "amount", "type", "executed_at", "loanable_id", "user_id", "expense_tag_id", "locked"];
    protected $attributes = [
        "type" => "credit",
        "locked" => "false",
    ];

    public $items = ["user", "loanable", "tag"];

    public $collections = ["changes"];

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

    public function changes()
    {
        return $this->hasMany(ExpenseChange::class);
    }

    public static function boot()
    {
        parent::boot();

        Expense::observe(new ExpensesObserver);

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
            ->where(function ($q) use ($user) {
                // ... expenses payed by himself or herself
                $q->where('user_id', $user->id)
                // ...or expenses payed by somebody belonging to the same community
                ->orWhereIn('user_id', $user->getSameCommunityUserIds());
            })
            // ...and expenses that is for a loanable belonging to the same community
            ->orWhereHas("loanable", function ($q) use ($user) {
                return $q->whereHas("owner", function ($q) use ($user) {
                    return $q->whereIn('user_id', $user->getSameCommunityUserIds());
                })
                // case of o loanable without owner
                ->orWhereHas("community", function ($q) use ($user) {
                    return $q->whereIn('id', $user->getAccessibleCommunityIds());
                });
            });
    }
}
