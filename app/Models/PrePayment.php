<?php

namespace App\Models;

use Carbon\Carbon;

class PrePayment extends Action
{
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
                $model->executed_at = Carbon::now();

                switch ($model->status) {
                    case 'completed':
                        if (!$model->loan->takeover) {
                            $takeover = Takeover::create([ 'loan_id' => $model->loan->id ]);
                            $model->loan->takeover()->save($takeover);
                        }
                        break;
                    case 'canceled':
                        $model->loan->setCanceled();
                        break;
                }

                $model->save();
            }
        });
    }

    public static function getColumnsDefinition() {
        return [
            '*' => function ($query = null) {
                if (!$query) {
                    return 'pre_payments.*';
                }

                return $query->selectRaw('pre_payments.*');
            },
            'type' => function ($query = null) {
                if (!$query) {
                    return "'pre_payment' AS type";
                }

                return $query->selectRaw("'pre_payment' AS type");
            }
        ];
    }
}
