<?php

namespace App\Models;

use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use App\Transformers\BillableItemTransformer;

class BillableItem extends BaseModel
{
    public static $rules = [
        'label' => 'required',
        'amount' => 'required|numeric',
    ];

    protected $fillable = [
        'label',
        'amount',
        'bill_id',
        'payment_id',
    ];

    public static $transformer = BillableItemTransformer::class;

    public $items = ['bill', 'payment'];

    public function bill() {
        return $this->belongsTo(Bill::class);
    }

    public function payment() {
        return $this->hasOne(Payment::class);
    }
}
