<?php

namespace App\Models;

use App\Models\Action;
use App\Models\Loan;
use App\Models\Payment;
use App\Transformers\IntentionTransformer;
use Carbon\Carbon;

class Intention extends Action
{
    protected $table = 'intentions';

    public static $rules = [
        'executed_at' => 'date',
    ];

    protected $fillable = [
        'loan_id',
    ];

    public static $transformer = IntentionTransformer::class;

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
                        $loanId = $model->loan->id;

                        //TODO payment creation
                        //$payment = Payment::create(['loan_id' => $loanId]);
                        //$model->loan->payments()->save($payment);

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
