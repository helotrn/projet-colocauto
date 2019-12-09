<?php

namespace App\Models;

use App\Models\Loan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
