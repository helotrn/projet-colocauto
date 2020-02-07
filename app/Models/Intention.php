<?php

namespace App\Models;

use App\Models\Action;
use App\Transformers\IntentionTransformer;

class Intention extends Action
{
    public static $rules = [
        'status' => 'required',
    ];

    protected $fillable = [
        'status',
    ];

    public static $transformer = IntentionTransformer::class;
}
