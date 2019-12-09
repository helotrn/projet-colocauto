<?php

namespace App\Models;

use App\Models\Loanable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\CarTransformer;

class Car extends Loanable
{
    public static $rules = [
        'name' => 'required',
        'position' => 'required',
        'location_description' => 'required',
        'comments' => 'required',
        'instructions' => 'required',
        'brand' => 'required',
        'model' => 'required',
        'year_of_circulation' => 'required|digits:4',
        'transmission_mode' => 'required',
        'fuel' => 'required',
        'plate_number' => 'required',
        'is_value_over_fifty_thousand' => 'boolean',
        'owners' => 'required',
        'papers_location' => 'required',
        'has_accident_report' => 'accepted',
        'insurer' => 'required',
        'has_informed_insurer' => 'accepted',
    ];
    
    protected $fillable = [
        'name',
        'position',
        'location_description',
        'comments',
        'instructions',
        'brand',
        'model',
        'year_of_circulation',
        'transmission_mode',
        'fuel',
        'plate_number',
        'is_value_over_fifty_thousand',
        'owners',
        'papers_location',
        'has_accident_report',
        'insurer',
        'has_informed_insurer',
    ];

    public static $transformer = CarTransformer::class;
}
