<?php

namespace App\Models;

use App\Models\Loanable;

class Trailer extends Loanable
{
    protected $table = 'trailers';

    public static $rules = [
    ];

    protected $fillable = [
        'availability_ics',
        'name',
        'position',
        'location_description',
        'comments',
        'instructions',
        'maximum_charge',
    ];

    public static function getColumnsDefinition() {
        return [
            '*' => function ($query = null) {
                if (!$query) {
                    return 'trailers.*';
                }

                return $query->selectRaw('trailers.*');
            },
            'type' => function ($query = null) {
                if (!$query) {
                    return "'trailer' AS type";
                }

                return $query->selectRaw("'trailer' AS type");
            }
        ];
    }
}
