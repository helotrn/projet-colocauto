<?php

namespace App\Models;

use App\Models\Loanable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\TrailerTransformer;

class Trailer extends Loanable
{
    public static $rules = [
        'name' => 'required',
        'position' => 'required',
        'location_description' => 'required|string',
        'comments' => 'required|string',
        'instructions' => 'required|string',
        'type' => 'required',
        'maximum_charge' => 'required',
    ];
    
    protected $fillable = [
        'name',
        'position',
        'location_description',
        'comments',
        'instructions',
        'type',
        'maximum_charge',
    ];

    public static $transformer = TrailerTransformer::class;
}
