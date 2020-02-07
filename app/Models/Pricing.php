<?php

namespace App\Models;

use App\Models\Community;
use App\Transformers\PricingTransformer;

class Pricing extends BaseModel
{
    public static $rules = [
        'name' => 'required',
        'object_type' => 'required',
        'variable' => 'required',
        'rule' => 'required',
    ];

    protected $fillable = [
        'name',
        'object_type',
        'variable',
        'rule',
    ];

    public static $transformer = PricingTransformer::class;

    public $belongsTo = ['community'];

    public function community() {
        return $this->belongsTo(Community::class);
    }
}
