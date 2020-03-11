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
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends BaseModel
{
    use SoftDeletes;

    public static $rules = [
        'departure_at' => [
            'nullable',
            'date',
        ],
        'duration_in_minutes' => [
            'nullable',
        ],
        'estimated_distance' => [
            'integer',
            'required'
        ],
    ];

    protected $fillable = [
        'departure_at',
        'duration_in_minutes',
        'estimated_distance',
        'borrower_id',
    ];

    public static function boot() {
        parent::boot();

        self::created(function ($model) {
            if ($model->intentions()->count() == 0) {
                $intention = Intention::create(['loan_id' => $model->id]);

                $model->intentions()->save($intention);
            }
        });
    }

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
        return $this->morphTo();
    }

    public function setCanceled() {
        //TODO
    }

    public function getPrice() {
        return 12.22; // FIXME
    }
}
