<?php

namespace App\Models;

use App\Models\User;

class PaymentMethod extends BaseModel
{
    public static $rules = [
        'name' => 'required',
        'external_id' => 'required',
        'type' => 'required',
        'four_last_digits' => 'digits:4|nullable',
        'credit_card_type' => 'nullable|string',
    ];

    protected $fillable = [
        'name',
        'external_id',
        'type',
        'four_last_digits',
        'credit_card_type',
    ];

    public $items = ['user'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
