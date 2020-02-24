<?php

namespace App\Models\Pivots;

use App\Models\Image;

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

    protected $with = ['proof'];

    public $morphOnes = [
        'proof' => 'imageable',
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
}
