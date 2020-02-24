<?php

namespace App\Models;

use App\Models\BillableItem;
use App\Models\User;
use App\Transformers\BillTransformer;
use Carbon\Carbon;

class Bill extends BaseModel
{
    public static $rules = [
        'period' => 'required',
    ];

    public static $transformer = BillTransformer::class;

    protected $fillable = [
        'period',
    ];

    public $items = ['user'];

    public function user() {
        return $this->belongsTo(User::class);
    }


    public $collections = ['billableItems'];

    public function billableItems() {
        return $this->hasMany(BillableItem::class);
    }
}
