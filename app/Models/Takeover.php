<?php

namespace App\Models;

use App\Models\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\TakeoverTransformer;

class Takeover extends Action
{
    public static $rules = [
        'status' => 'required',
        'mileage_beginning' => 'required',
        'fuel_beginning' => 'required',
        'comments_on_vehicle' => 'nullable',
    ];
    
    protected $fillable = [
        'status',
        'mileage_beginning',
        'fuel_beginning',
        'comments_on_vehicle',
    ];

    public static $transformer = TakeoverTransformer::class;
}
