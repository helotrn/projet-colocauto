<?php

namespace App\Models;

use App\Models\Action;
use App\Transformers\IntentionTransformer;

class Intention extends Action
{
    protected $table = 'intentions';

    public static $rules = [
        'status' => 'required',
    ];

    protected $fillable = [
        'status',
    ];

    public static $transformer = IntentionTransformer::class;

    public static function getColumnsDefinition() {
        return [
            '*' => function ($query = null) {
                if (!$query) {
                    return 'intentions.*';
                }

                return $query->selectRaw('intentions.*');
            },
            'type' => function ($query = null) {
                if (!$query) {
                    return "'intention' AS type";
                }

                return $query->selectRaw("'intention' AS type");
            }
        ];
    }
}
