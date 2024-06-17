<?php

namespace App\Models;

use App\Models\Community;
use App\Casts\TimestampWithTimezoneCast;
use Illuminate\Database\Eloquent\Builder;

class Invitation extends BaseModel
{
    public static $rules = [
        "email" => ["required", "email"],
        "community_id" => ["requiredIf:for_community_admin,false"],
        "consumed_at" => ["nullable", "date"],
    ];

    protected $fillable = ["community_id", "email", "for_community_admin"];

    protected $hidden = [];

    public $items = ["community", "token"];

    protected $casts = [
        "consumed_at" => TimestampWithTimezoneCast::class,
    ];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function consume()
    {
        $this->consumed_at = new \DateTime();
        $this->save();
    }

    public function scopeAccessibleBy(Builder $query, $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        // a user has access to ...
        return $query->where(function ($q) use ($user) {
            // ... invitations in communities ...
            return $q->whereHas("community", function ($q) use ($user) {
                // ... where he or she has access
                $q->accessibleBy($user);
            });
        });
    }
}
