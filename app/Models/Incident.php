<?php

namespace App\Models;

use App\Models\Action;
use App\Transformers\ImageTransformer;

class Incident extends Action
{
    public static $rules = [
        'status' => 'required',
        'incident_type' => 'required',
    ];

    protected $fillable = [
        'status',
        'incident_type',
    ];

    public static $transformer = IncidentTransformer::class;
}
