<?php

namespace App\Models;

use Carbon\Carbon;

class Payment extends Action
{
    protected $fillable = [
        'loan_id',
        'bill_item_id',
    ];

    public $items = ['bill_item', 'loan'];

    public function billItem() {
        return $this->belongsTo(BillItem::class);
    }

    public function loan() {
        return $this->belongsTo(Loan::class);
    }

    public static function boot() {
        parent::boot();

        self::saved(function ($model) {
            if (!$model->executed_at) {
                switch ($model->status) {
                    case 'completed':
                        $model->executed_at = Carbon::now();

                        $model->save();
                        break;
                    case 'canceled':
                        $model->executed_at = Carbon::now();
                        $model->save();
                        $model->loan->setCanceled();
                        break;
                }
            }
        });
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
