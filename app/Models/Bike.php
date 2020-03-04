<?php

namespace App\Models;

use App\Models\Loanable;

class Bike extends Loanable
{
    protected $table = 'bikes';

    public static $rules = [
        'name' => [ 'required' ],
        'position' => [ 'required' ],
        'type' => [
            'required',
            'in:bike',
        ],
        'instructions' => [ 'present' ],
        'comments' => [ 'present' ],
        'model' => [ 'present' ],
        'bike_type' => [ 'required' ],
        'size' => [ 'required' ],
    ];

    protected $fillable = [
        'bike_type',
        'comments',
        'instructions',
        'location_description',
        'model',
        'name',
        'position',
        'size',
    ];

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
