<?php

namespace App\Models;

use App\Models\Action;
use App\Transformers\ExtensionTransformer;

class Extension extends Action
{
    public static $rules = [
        'status' => 'required',
        'new_duration' => 'required',//add validation
        'comments_on_extension' => 'required|string',
    ];

    protected $fillable = [
        'status',
        'new_duration',
        'comments_on_extension',
    ];

    public static $transformer = ExtensionTransformer::class;
}
