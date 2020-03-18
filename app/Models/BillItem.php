<?php

namespace App\Models;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillItem extends BaseModel
{
    use SoftDeletes;

    public static $rules = [
        'label' => 'required',
        'amount' => 'required|numeric',
    ];

    protected $fillable = [
        'label',
        'amount',
        'invoice_id',
        'payment_id',
        'item_date',
    ];

    public $items = ['invoice', 'payment'];

    public function invoice() {
        return $this->belongsTo(Invoice::class);
    }

    public function payment() {
        return $this->hasOne(Payment::class);
    }
}
