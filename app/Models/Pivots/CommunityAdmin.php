<?php

namespace App\Models\Pivots;

use App\Models\Community;
use App\Models\File;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class CommunityAdmin extends BasePivot
{
    public static function boot()
    {
        parent::boot();
    }

    protected $fillable = [
        "community_id",
        "organisation",
        "suspended_at",
        "user_id",
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'community_admin');
    }

    public function community()
    {
        return $this->belongsTo(Community::class, 'community_admin');
    }

    public function scopeAccessibleBy(Builder $query, $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->whereHas("user", function ($q) use ($user) {
            return $q->accessibleBy($user);
        });
    }
}
