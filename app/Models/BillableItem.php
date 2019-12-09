<?php

namespace App\Models;

use App\Models\Bill;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
    ];

    public static $transformer = BillableItemTransformer::class;

    public $belongsTo = ['bill'];

    public function bill() {
        return $this->belongsTo(Bill::class);
    }
}
