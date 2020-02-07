<?php

namespace App\Models;

use App\Models\Loan;

class Action extends BaseModel
{
    protected $fillable = [
        'name',
    ];

    public $belongsTo = ['loan'];

    public function loan() {
        return $this->belongsTo(Loan::class);
    }
}
