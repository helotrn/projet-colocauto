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
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends BaseModel
{
    use SoftDeletes;

    public static $rules = [
        'departure_at' => [
            'required',
        ],
        'duration_in_minutes' => [
            'integer',
            'required',
        ],
        'estimated_distance' => [
            'integer',
            'required'
        ],
        'estimated_price' => [
            'numeric',
            'required',
        ],
        'message_for_owner' => [ 'present' ],
        'reason' => [ 'required' ],
    ];

    public static $transformer = LoanTransformer::class;

    public static function boot() {
        parent::boot();

        self::created(function ($model) {
            if (!$model->intention) {
                $intention = Intention::create(['loan_id' => $model->id]);

                $model->intention()->save($intention);
            }
        });
    }

    protected $fillable = [
        'departure_at',
        'duration_in_minutes',
        'estimated_distance',
        'estimated_price',
        'message_for_owner',
        'reason',
    ];

    public $items = ['borrower', 'intention', 'loanable'];

    public $collections = ['actions'];

    public function actions() {
        return $this->hasMany(Action::class)->orderBy('created_at', 'asc');
    }

    public function borrower() {
        return $this->belongsTo(Borrower::class);
    }

    public function extensions() {
        return $this->hasMany(Extension::class);
    }

    public function handover() {
        return $this->hasOne(Handover::class);
    }

    public function incidents() {
        return $this->hasMany(Incident::class);
    }

    public function intention() {
        return $this->hasOne(Intention::class);
    }

    public function loanable() {
        return $this->belongsTo(Loanable::class);
    }

    public function payment() {
        return $this->hasOne(Payment::class);
    }

    public function prePayment() {
        return $this->hasOne(PrePayment::class);
    }

    public function takeover() {
        return $this->hasOne(Takeover::class);
    }

    public function setCanceled() {
        //TODO
    }

    public function getPrice() {
        return 12.22; // FIXME
    }
}
