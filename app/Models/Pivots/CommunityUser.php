<?php

namespace App\Models\Pivots;

use App\Models\Community;
use App\Models\Image;
use App\Models\Tag;

class CommunityUser extends BasePivot
{
    public static $sizes = [
        'thumbnail' => '256x@fit',
    ];

    protected $fillable = [
        'approved_at',
        'community_id',
        'role',
        'suspended_at',
        'user_id',
    ];

    protected $with = ['proof', 'tags'];

    public $morphOnes = [
        'proof' => 'imageable',
    ];

    public $morphManys = [
        'tags' => 'taggable',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function community() {
        return $this->belongsTo(Community::class);
    }

    public function proof() {
        return $this->morphOne(Image::class, 'imageable')->where('field', 'proof');
    }

    public function tags() {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
