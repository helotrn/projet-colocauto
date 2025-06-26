<?php

namespace App\Models;

use App\Models\Community;
use App\Models\User;
use App\Casts\TimestampWithTimezoneCast;
use Illuminate\Database\Eloquent\Builder;

class Invitation extends BaseModel
{
    public static $rules = [
        "email" => ["required", "email"],
        "community_id" => ["nullable"],
        "consumed_at" => ["nullable", "date"],
    ];

    public static $filterTypes = [
        "id" => "number",
        "email" => "text",
        "token" => "text",
        "community.id" => [
            "type" => "relation",
            "query" => [
                "slug" => "communities",
                "value" => "id",
                "text" => "name",
                "params" => [
                    "fields" => "id,name",
                ]
            ]
        ],
        "user.id" => [
            "type" => "relation",
            "query" => [
                "slug" => "unsers",
                "value" => "id",
                "text" => "full_name",
                "params" => [
                    "fields" => "id,full_name",
                ]
            ]
        ],
    ];

    protected $fillable = ["community_id", "email", "for_community_admin"];

    protected $hidden = [];

    public $items = ["community", "user"];

    protected $casts = [
        "consumed_at" => TimestampWithTimezoneCast::class,
    ];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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
            })->orWhereHas("user", function ($q) use ($user) {
                $q->where("id", $user->id);
            });
        });
    }
}
