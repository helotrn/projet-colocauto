<?php

namespace App\Models;

use App\Models\Action;
use App\Models\BillableItem;
use Illuminate\Database\Eloquent\Builder;
use App\Transformers\PaymentTransformer;

class Payment extends Action
{
    public static $rules = [
        'status' => 'required',
    ];

    protected $fillable = [
        'status',
    ];

    public static $transformer = PaymentTransformer::class;

    public $items = ['billableItem'];

    public function billableItem() {
        return $this->belongsTo(BillableItem::class);
    }
}
