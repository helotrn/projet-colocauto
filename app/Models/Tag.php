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

    public $collections = [
        'users',
    ];

    public function users() {
        return $this->morphedByMany(User::class, 'taggable');
    }
}
