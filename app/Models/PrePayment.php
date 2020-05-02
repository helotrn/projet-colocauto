<?php

namespace App\Models;

use Carbon\Carbon;

class PrePayment extends Action
{
    public static function boot() {
        parent::boot();

        self::saved(function ($model) {
            if ($model->executed_at) {
                return;
            }

            $loan = $model->loan;
            $borrowerUser = $loan->borrower->user;
            if ($borrowerUser->balance >= $loan->estimated_price) {
                $model->status = 'completed';
            }

            switch ($model->status) {
                case 'completed':
                    if (!$model->loan->takeover) {
                        $takeover = new Takeover();
                        $takeover->loan()->associate($model->loan);
                        $takeover->save();
                    }

                    $model->executed_at = Carbon::now();
                    $model->save();
                    break;
                case 'canceled':
                    $model->executed_at = Carbon::now();
                    $model->save();
                    break;
                default:
                    break;
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

    public $readOnly = false;

    protected $fillable = [];

    public $items = ['loan'];

    public function loan() {
        return $this->belongsTo(Loan::class);
    }
}
