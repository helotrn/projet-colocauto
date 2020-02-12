<?php

namespace App\Models;

use App\Models\Action;
use App\Transformers\IntentionTransformer;

class Intention extends Action
{
    protected $table = 'intentions';

    public static $rules = [
        'executed_at' => 'date_format:"Y-m-d H:i:s"',
        'status' => 'required',
    ];

    protected $fillable = [
        'executed_at',
        'status',
        'loan_id',
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
