<?php

namespace App\Models;

use App\Models\Action;
use Illuminate\Database\Eloquent\Builder;
use App\Transformers\TakeoverTransformer;

class Takeover extends Action
{
    protected $table = 'takovers';

    public static $rules = [
        'status' => 'required',
        'mileage_beginning' => 'required',
        'fuel_beginning' => 'required',
        'comments_on_vehicle' => 'nullable',
    ];

    protected $fillable = [
        'status',
        'mileage_beginning',
        'fuel_beginning',
        'comments_on_vehicle',
    ];

    public static $transformer = TakeoverTransformer::class;

    public static function getColumnsDefinition() {
        return [
            '*' => function ($query = null) {
                if (!$query) {
                    return 'takeovers.*';
                }

                return $query->selectRaw('takeovers.*');
            },
            'type' => function ($query = null) {
                if (!$query) {
                    return "'takeover' AS type";
                }

                return $query->selectRaw("'takeover' AS type");
            }
        ];
    }
}
