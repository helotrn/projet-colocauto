<?php

namespace App\Models;

use Carbon\Carbon;

class Payment extends Action
{
    public static function boot() {
        parent::boot();

        self::saved(function ($model) {
            if ($model->executed_at) {
                return;
            }

            switch ($model->status) {
                case 'completed':
                    $loan = $model->loan;
                    $price = $loan->actual_price;

                    $borrower = $loan->borrower->user;
                    $borrower->removeFromBalance($price);

                    $owner = $loan->loanable->owner->user;
                    $owner->addToBalance($price);

                    $loan->final_price = $price;
                    $loan->save();

                    $model->executed_at = Carbon::now();
                    $model->save();
                    break;
                case 'canceled':
                    $model->executed_at = Carbon::now();
                    $model->save();
                    break;
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

    protected $fillable = [
        'loan_id',
        'bill_item_id',
    ];

    public $readOnly = false;

    public $items = ['bill_item', 'loan'];

    public function billItem() {
        return $this->belongsTo(BillItem::class);
    }

    public function loan() {
        return $this->belongsTo(Loan::class);
    }
}
