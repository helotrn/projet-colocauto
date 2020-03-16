<?php

namespace App\Models;

use Carbon\Carbon;

class Intention extends Action
{
    protected $fillable = [
        'loan_id',
        'message_for_borrower',
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
                        if (!$model->loan->prePayment) {
                            $prePayment = PrePayment::create(['loan_id' => $model->loan->id]);
                            $model->loan->prePayment()->save($prePayment);
                            $model->executed_at = Carbon::now();

                            $model->save();
                        }
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
