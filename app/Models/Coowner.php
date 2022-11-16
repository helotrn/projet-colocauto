<?php

namespace App\Models;

class Coowner extends BaseModel
{
    public $items = ["user", "loanable"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loanable()
    {
        return $this->belongsTo(Loanable::class);
    }
}
