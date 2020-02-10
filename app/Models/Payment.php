<?php

namespace App\Models;

use App\Models\Action;
use App\Models\BillableItem;
use Illuminate\Database\Eloquent\Builder;
use App\Transformers\PaymentTransformer;

class Payment extends Action
{
    protected $table = 'payments';

    public static $rules = [
        'status' => 'required',
    ];

    protected $fillable = [
        'status',
    ];

    public static $transformer = PaymentTransformer::class;

    public $belongsTo = ['billableItem'];

    public function billableItem() {
        return $this->belongsTo(BillableItem::class);
    }

    public static function getColumnsDefinition() {
        return [
            '*' => function ($query = null) {
                if (!$query) {
                    return 'payments.*';
                }

                return $query->selectRaw('payments.*');
            },
            'type' => function ($query = null) {
                if (!$query) {
                    return "'payment' AS type";
                }

                return $query->selectRaw("'payment' AS type");
            }
        ];
    }
}
