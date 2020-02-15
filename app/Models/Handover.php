<?php

namespace App\Models;

use App\Models\Action;
use App\Models\Loan;
use Illuminate\Database\Eloquent\Builder;
use App\Transformers\HandoverTransformer;

class Handover extends Action
{
    protected $table = 'handovers';

    public static $rules = [
        'status' => 'required',
        'mileage_end' => 'required',
        'fuel_end' => 'required',
        'comments_by_borrower' => 'nullable',
        'comments_by_owner' => 'nullable',
        'purchases_amount' => 'required',//add validation
    ];

    protected $fillable = [
        'status',
        'mileage_end',
        'fuel_end',
        'comments_by_borrower',
        'comments_by_owner',
        'purchases_amount',
    ];

    public static $transformer = HandoverTransformer::class;

    public function loan() {
        return $this->belongsTo(Loan::class);
    }

    public static function getColumnsDefinition() {
        return [
            '*' => function ($query = null) {
                if (!$query) {
                    return 'handovers.*';
                }

                return $query->selectRaw('handovers.*');
            },
            'type' => function ($query = null) {
                if (!$query) {
                    return "'handover' AS type";
                }

                return $query->selectRaw("'handover' AS type");
            }
        ];
    }
}
