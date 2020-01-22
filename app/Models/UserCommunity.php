<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use App\Models\Community;
use App\Models\User;
use App\Transformers\UserCommunityTransformer;

class UserCommunity extends AuthenticatableBaseModel
{

    public static $rules = [
        'role' => 'nullable',
    ];

    public static $transformer = UserCommunityTransformer::class;

    public static function getColumnsDefinition() {
        return [
            '*' => function ($query = null) {
                return $query->selectRaw('community_user.*');
            },
        ];
    }

    protected $fillable = [
        'role',
    ];

    public function communities() {
        return $this->hasMany(Community::class);
    }

    public function users() {
        return $this->hasMany(User::class);
    }
}
