<?php

namespace App\Models;

use App\Models\Loanable;
use App\Transformers\BikeTransformer;

class Bike extends Loanable
{
    protected $table = 'bikes';

    public static $rules = [
    ];

    protected $fillable = [
        'availability_ics',
        'bike_type',
        'comments',
        'instructions',
        'location_description',
        'model',
        'name',
        'position',
        'size',
    ];

    public static $transformer = BikeTransformer::class;

    public static function getColumnsDefinition() {
        return [
            '*' => function ($query = null) {
                if (!$query) {
                    return 'bikes.*';
                }

                return $query->selectRaw('bikes.*');
            },
            'type' => function ($query = null) {
                if (!$query) {
                    return "'bike' AS type";
                }

                return $query->selectRaw("'bike' AS type");
            }
        ];
    }
}
