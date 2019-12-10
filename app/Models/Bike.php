<?php

namespace App\Models;

use App\Models\Loanable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\BikeTransformer;

class Bike extends Loanable
{
    protected $table = 'bikes';

    public static $rules = [
        'name' => 'required',
        'position' => 'required',
        'location_description' => 'required',
        'comments' => 'required',
        'instructions' => 'required',
        'model' => 'required',
        'type' => 'required',
        'size' => 'required',
    ];

    protected $fillable = [
        'name',
        'position',
        'location_description',
        'comments',
        'instructions',
        'model',
        'type',
        'size',
    ];

    public static $transformer = BikeTransformer::class;
}
