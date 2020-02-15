<?php

namespace App\Models;

use App\Models\Action;
use App\Models\Loan;
use App\Models\Payment;
use App\Transformers\IntentionTransformer;

class Intention extends Action
{
    protected $table = 'intentions';

    public static $rules = [
        'executed_at' => 'date',
        'status' => 'required',
    ];

    protected $fillable = [
        'status',
        'loan_id',
    ];

    public static $transformer = IntentionTransformer::class;

    public $items = ['loan'];

    public function loan() {
        return $this->belongsTo(Loan::class);
    }

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
