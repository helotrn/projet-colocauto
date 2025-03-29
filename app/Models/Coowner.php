<?php

namespace App\Models;

class Coowner extends BaseModel
{
    public $items = ["user", "loanable"];
    public static $rules = [
        "title" => "nullable|string",
        "receive_notifications" => "boolean",
        "pays_loan_price" => "boolean",
        "pays_provisions" => "boolean",
        "pays_owner" => "boolean",
    ];
    protected $attributes = [
        "receive_notifications" => false,
        "pays_loan_price" => true,
        "pays_provisions" => true,
        "pays_owner" => true,
    ];

    public $fillable = ["title", "receive_notifications", "pays_loan_price", "pays_provisions", "pays_owner"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loanable()
    {
        return $this->belongsTo(Loanable::class);
    }
}
