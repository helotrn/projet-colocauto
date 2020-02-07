<?php

namespace App\Models;

use App\Models\Loanable;
use App\Transformers\TrailerTransformer;

class Trailer extends Loanable
{
    protected $table = 'trailers';

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
