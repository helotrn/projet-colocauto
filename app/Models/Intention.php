<?php

namespace App\Models;

use App\Models\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
