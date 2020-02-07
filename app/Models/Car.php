<?php

namespace App\Models;

use App\Models\Loanable;
use App\Transformers\CarTransformer;

class Car extends Loanable
{
    protected $table = 'cars';

    public static $rules = [
        'availability_ics' => 'required',
        'brand' => 'required',
        'comments' => 'required',
        'engine' => 'required',
        'has_accident_report' => 'accepted',
        'has_informed_insurer' => 'accepted',
        'instructions' => 'required',
        'insurer' => 'required',
        'is_value_over_fifty_thousand' => 'boolean',
        'location_description' => 'required',
        'model' => 'required',
        'name' => 'required',
        'ownership' => 'required',
        'papers_location' => 'required',
        'plate_number' => 'required',
        'position' => 'required',
        'transmission_mode' => 'required',
        'year_of_circulation' => 'required|digits:4',
    ];

    protected $fillable = [
        'availability_ics',
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

    public static $transformer = CarTransformer::class;

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
}
