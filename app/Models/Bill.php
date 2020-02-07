<?php

namespace App\Models;

use App\Models\BillableItem;
use App\Models\User;
use App\Transformers\BillTransformer;

class Bill extends BaseModel
{
    public static $rules = [
        'period' => 'required',
        'payment_method' => 'required',
        'total' => 'required|numeric',
    ];

    protected $fillable = [
        'period',
        'payment_method',
        'total',
    ];

    public static $transformer = BillTransformer::class;

    public $belongsTo = ['user'];

    public function user() {
        return $this->belongsTo(User::class);
    }


    public $collections = ['billableItems'];

    public function billableItems() {
        return $this->hasMany(BillableItem::class);
    }
}
