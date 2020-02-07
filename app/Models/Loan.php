<?php

namespace App\Models;

use App\Models\Borrower;
use App\Transformers\LoanTransformer;

class Loan extends BaseModel
{
    public static $rules = [
        'departure_at' => 'required|date',
        'duration' => 'required',
    ];

    protected $fillable = [
        'departure_at',
        'duration',
    ];

    public static $transformer = LoanTransformer::class;

    public $belongsTo = ['borrower'];

    public function borrower() {
        return $this->belongsTo(Borrower::class);
    }
}
