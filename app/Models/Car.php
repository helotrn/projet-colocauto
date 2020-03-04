<?php

namespace App\Models;

use App\Models\Loanable;

class Car extends Loanable
{
    public static $rules = [
        'name' => [ 'required' ],
        'position' => [ 'required' ],
        'type' => [
            'required',
            'in:car'
        ],
        'location_description' => [ 'present' ],
        'instructions' => [ 'present' ],
        'comments' => [ 'present' ],
        'model' => [ 'required' ],
        'brand' => [ 'required' ],
        'engine' => [
            'required',
            'in:fuel,diesel,electric,hybrid',
        ],
        'insurer' => [ 'required' ],
        'is_value_over_fifty_thousand' => [ 'boolean' ],
        'ownership' => [
            'required',
            'in:self,rental',
        ],
        'papers_location' => [
            'required' ,
            'in:in_the_car,to_request_with_car'
        ],
        'plate_number' => [ 'required' ],
        'transmission_mode' => [
            'required' ,
            'in:manual,automatic',
        ],
        'year_of_circulation' => [
            'required',
            'digits:4',
        ],
    ];

    public static function getColumnsDefinition() {
        return [
            '*' => function ($query = null) {
                if (!$query) {
                    return 'cars.*';
                }

                return $query->selectRaw('cars.*');
            },
            'type' => function ($query = null) {
                if (!$query) {
                    return "'car' AS type";
                }

                return $query->selectRaw("'car' AS type");
            }
        ];
    }

    protected $table = 'cars';

    protected $fillable = [
        'availability_json',
        'availability_mode',
        'brand',
        'comments',
        'engine',
        'has_accident_report',
        'has_informed_insurer',
        'instructions',
        'insurer',
        'is_value_over_fifty_thousand',
        'location_description',
        'model',
        'name',
        'ownership',
        'papers_location',
        'plate_number',
        'position',
        'transmission_mode',
        'year_of_circulation',
    ];
}
