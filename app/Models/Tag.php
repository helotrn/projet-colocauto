<?php

namespace App\Models;

class Tag extends BaseModel
{
    public static $rules = [
        'name' => 'required|string',
        'type' => 'required',
    ];

    protected $fillable = [
        'name',
        'type',
    ];

    public function taggable() {
        return $this->morphTo();
    }
}
