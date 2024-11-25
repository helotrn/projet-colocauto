<?php

namespace App\Models;

class Coowner extends BaseModel
{
    public $items = ["user", "loanable"];
    public static $rules = [
        "title" => "nullable|string",
        "receive_notifications" => "boolean",
    ];

    public $fillable = ["title", "receive_notifications"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loanable()
    {
        return $this->belongsTo(Loanable::class);
    }
}
