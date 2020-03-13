<?php

namespace App\Models;

use App\Models\Action;
use App\Models\Loan;

class Handover extends Action
{
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
