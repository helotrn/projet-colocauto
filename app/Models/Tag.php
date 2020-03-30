<?php

namespace App\Models;

class Tag extends BaseModel
{
    public static $rules = [
        'name' => 'required',
        'type' => 'required',
    ];

    protected $fillable = [
        'name',
        'type',
    ];

    public $morphManys = [
        'users' => 'taggable',
        'community_users' => 'taggable',
    ];

    public function users() {
        return $this->morphedByMany(User::class, 'taggable');
    }

    public function communityUsers() {
        return $this->morphedByMany(Pivots\CommunityUser::class, 'taggable');
    }
}
