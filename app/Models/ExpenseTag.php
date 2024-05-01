<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Builder;

class ExpenseTag extends BaseModel
{
    public static $rules = [
        "name" => "required",
    ];

    protected $fillable = ["name", "slug", "color", "admin"];

    public $collections = ["expenses"];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public static function boot()
    {
        parent::boot();

        self::saved(function ($model) {
            if (!$model->color) {
                $model->color = 'primary';
                $model->save();
            }
            if (!$model->slug) {
                $model->slug = strtolower(preg_replace('/[^\w]+/', '_', $model->name));
                $model->save();
            }
        });
    }

    public function scopeAccessibleBy(Builder $query, $user)
    {
        // unless the tag is queried directly or by admin ...
        if ($user->isAdmin() ||
            $user->isCommunityAdmin() ||
            request()->routeIs("expense_tags.retrieve")
        ) {
            return $query;
        }
        // .. remove "admin" ones from the list
        return $query->where('admin', false);
    }

    public function scopeFor(Builder $query, $for, $user)
    {
        if (!$user) {
            $for = "read";
        }

        if ($user->isAdmin() || $for == "read") {
            return $query;
        }

        if ($for == "edit") {
            // non-admins cannot edit expense tags
            return $query->whereNotNull(\DB::raw('NULL'));
        }
    }
}
