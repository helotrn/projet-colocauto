<?php

namespace App\Models;

use App\Models\Action;
use App\Models\Loan;
use App\Models\Payment;
use Carbon\Carbon;

class Intention extends Action
{
    protected $table = 'intentions';

    public static $rules = [
    ];

    protected $fillable = [
        'loan_id',
    ];

    public $items = ['loan'];

    public function loan() {
        return $this->belongsTo(Loan::class);
    }

    public static function boot() {
        parent::boot();

        self::saved(function ($model) {
            if (!$model->executed_at) {
                switch ($model->status) {
                    case 'completed':
                        $user = $model->loan->borrower->user;

                        $bill = $user->getLastBillOrCreate();

                        $billableItem = BillableItem::create([
                            'bill_id' => $bill->id,
                            'label' => 'Payment', // FIXME
                            'amount' => $model->loan->getPrice(),
                            'item_date' => date('Y-m-d'),
                        ]);

                        $payment = Payment::create([
                            'loan_id' => $model->loan->id,
                            'billable_item_id' => $billableItem->id,
                        ]);
                        $model->loan->payments()->save($payment);

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
