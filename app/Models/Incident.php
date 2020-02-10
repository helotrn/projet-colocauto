<?php

namespace App\Models;

use App\Models\Action;
use App\Transformers\ImageTransformer;

class Incident extends Action
{
    protected $table = 'incidents';

    public static $rules = [
        'status' => 'required',
        'incident_type' => 'required',
    ];

    protected $fillable = [
        'status',
        'incident_type',
    ];

    public static $transformer = IncidentTransformer::class;

    public static function getColumnsDefinition() {
        return [
            '*' => function ($query = null) {
                if (!$query) {
                    return 'incidents.*';
                }

                return $query->selectRaw('incidents.*');
            },
            'type' => function ($query = null) {
                if (!$query) {
                    return "'incident' AS type";
                }

                return $query->selectRaw("'incident' AS type");
            }
        ];
    }
}
