<?php

namespace App\Models;

use App\Models\Loan;
use App\Models\User;

class Action extends BaseModel
{
    protected $fillable = [
        'name',
    ];

    public $belongsTo = ['loan', 'user'];

    public function loan() {
        return $this->belongsTo(Loan::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
