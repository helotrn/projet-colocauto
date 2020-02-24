<?php

namespace App\Models;

use App\Models\Action;
use App\Models\BillableItem;
use App\Models\Loan;
use App\Transformers\PaymentTransformer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class Payment extends Action
{
    protected $table = 'payments';

    public static $rules = [
    ];

    protected $fillable = [
        'loan_id',
        'billable_item_id',
    ];

    public static $transformer = PaymentTransformer::class;

    public $items = ['billableItem', 'loan'];

    public function billableItem() {
        return $this->belongsTo(BillableItem::class);
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
                        //$model->loan->actions->create(new Takeover);
                        //TODO Takeover creation

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
