<?php

namespace App\Models;

use App\Models\Loan;
use App\Models\User;

class Action extends BaseModel
{

    public $items = ['loan', 'user'];

    public function loan() {
        return $this->morphOne(Loan::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
