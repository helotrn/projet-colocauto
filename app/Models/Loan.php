<?php

namespace App\Models;

use App\Models\Borrower;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Loan extends BaseModel
{
    protected $fillable = [
        'name', 'object_type', 'variable', 'rule',
    ];

    public $belongsTo = ['borrower'];

    public function borrower() {
        return $this->belongsTo(Borrower::class);
    }
}
