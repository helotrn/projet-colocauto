<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\PadlockTransformer;

class Padlock extends BaseModel
{
    public static $rules = [
        'mac_address' => 'required',
    ];
    
    protected $fillable = [
        'mac_address',
    ];

    public static $transformer = PadlockTransformer::class;
}
