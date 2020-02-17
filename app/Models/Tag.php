<?php

namespace App\Models;

use App\Transformers\TagTransformer;

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

    public static $transformer = TagTransformer::class;

    public function taggable() {
        return $this->morphTo();
    }
}
