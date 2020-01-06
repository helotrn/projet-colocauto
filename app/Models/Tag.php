<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
}
