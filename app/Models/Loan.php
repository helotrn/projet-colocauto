<?php

namespace App\Models;

use App\Models\Action;
use App\Models\Borrower;
use App\Models\Extension;
use App\Models\Handover;
use App\Models\Incident;
use App\Models\Intention;
use App\Models\Loanable;
use App\Models\Payment;
use App\Models\Takeover;
use App\Transformers\LoanTransformer;

class Loan extends BaseModel
{
    public static $rules = [
        'departure_at' => 'nullable|timestamptz',
        'duration' => 'nullable',
    ];

    protected $fillable = [
        'departure_at',
        'duration',
    ];

    protected $casts = [
        'departure_at' => 'timestamptz',
    ];

    public static $transformer = LoanTransformer::class;

    public $collections = ['actions'];

    public function actions() {
        return $this->hasMany(Action::class);
    }

    public function payments() {
        return $this->hasMany(Payment::class);
    }

    public function takeovers() {
        return $this->hasMany(Takeover::class);
    }

    public function handovers() {
        return $this->hasMany(Handover::class);
    }

    public function incidents() {
        return $this->hasMany(Incident::class);
    }

    public function intentions() {
        return $this->hasMany(Intention::class);
    }

    public function extensions() {
        return $this->hasMany(Extension::class);
    }

    public $items = ['borrower', 'loanable'];

    public function borrower() {
        return $this->belongsTo(Borrower::class);
    }

    public function loanable() {
        return $this->belongsTo(Loanable::class);
    }
}
