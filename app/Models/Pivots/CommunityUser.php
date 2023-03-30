<?php

namespace App\Models\Pivots;

use App\Models\Community;
use App\Models\File;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class CommunityUser extends BasePivot
{
    public static $sizes = [
        "thumbnail" => "256x@fit",
    ];

    public static function boot()
    {
        parent::boot();

        self::saved(function ($model) {
            // approuve all community members by default
            if(!$model->approved_at) {
                $model->approved_at = new \Carbon\Carbon();
                $model->save();
            }
        });
    }

    protected $casts = [
        "approved_at" => "datetime",
    ];

    protected $fillable = [
        "approved_at",
        "community_id",
        "role",
        "suspended_at",
        "user_id",
    ];

    protected $with = ["proof", "tags"];

    public $morphManys = [
        "tags" => "taggable",
        "proof" => "fileable",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function proof()
    {
        return $this->morphMany(File::class, "fileable")->where(
            "field",
            "proof"
        );
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, "taggable");
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
