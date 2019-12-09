<?php

namespace App\Models;

use App\Models\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\HandoverTransformer;

class Handover extends Action
{
    public static $rules = [
        'status' => 'required',
        'mileage_end' => 'required',
        'fuel_end' => 'required',
        'comments_by_borrower' => 'nullable',
        'comments_by_owner' => 'nullable',
        'purchases_amount' => 'required',//add validation
    ];
    
    protected $fillable = [
        'status',
        'mileage_end',
        'fuel_end',
        'comments_by_borrower',
        'comments_by_owner',
        'purchases_amount',
    ];

    public static $transformer = HandoverTransformer::class;
}
