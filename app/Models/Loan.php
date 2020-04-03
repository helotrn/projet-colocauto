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
use Illuminate\Database\Eloquent\Builder;
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

    public static $filterTypes = [
      'loanable.owner.user.full_name' => 'text',
      'borrower.user.full_name' => 'text',
    ];

    public static function boot() {
        parent::boot();

        self::created(function ($model) {
            if (!$model->intention) {
                $intention = new Intention();
                $intention->loan()->associate($model);
                $intention->save();
            }
        });
    }

    public static function getColumnsDefinition() {
        return [
            '*' => function ($query = null) {
                if (!$query) {
                    return 'loans.*';
                }

                return $query->selectRaw('loans.*');
            },
            'status' => function ($query = null) {
                $query = static::addJoin(
                    $query,
                    'actions AS all_actions',
                    \DB::raw('all_actions.loan_id'),
                    '=',
                    \DB::raw('loans.id')
                );

                return $query
                    ->selectRaw('(array_agg(all_actions.status ORDER BY all_actions.id DESC))[1] AS status')
                    ->groupBy('loans.id');
            }
        ];
    }

    protected $fillable = [
        'departure_at',
        'duration_in_minutes',
        'estimated_distance',
        'estimated_price',
        'message_for_owner',
        'reason',
    ];

    public $computed = ['actual_price', 'actual_duration_in_minutes', 'canceled_at'];

    public $items = [
        'borrower',
        'community',
        'handover',
        'intention',
        'loanable',
        'payment',
        'pre_payment',
        'takeover',
    ];

    public $collections = ['actions'];

    public function actions() {
        return $this->hasMany(Action::class)->orderBy('id', 'asc');
    }

    public function borrower() {
        return $this->belongsTo(Borrower::class);
    }

    public function community() {
        return $this->belongsTo(Community::class);
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

    public function getActualDurationInMinutesAttribute() {
        return $this->duration_in_minutes;
    }

    public function getActualPriceAttribute() {
        $takeover = $this->takeover;
        $handover = $this->handover;

        if (!$takeover || !$handover) {
            return null;
        }

        if (!$takeover->executed_at || !$handover->executed_at) {
            return null;
        }

        $pricing = $this->community->getPricingFor($this->loanable);

        return $pricing->evaluateRule(
            $handover->mileage_end - $takeover->mileage_beginning,
            $this->actual_duration_in_minutes,
            $this->loanable
        );
    }

    public function getCanceledAtAttribute() {
        $canceledAction = $this->actions->where('status', 'canceled')->first();

        if ($canceledAction) {
            return $canceledAction->executed_at;
        }

        return null;
    }

    public function scopeAccessibleBy(Builder $query, $user) {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->owner) {
            $ownerId = $user->owner->id;
            $query = $query->whereHas('loanable', function ($q) use ($ownerId) {
                return $q->where('owner_id', $ownerId);
            });
        }

        if ($user->borrower) {
            $borrowerId = $user->borrower->id;
            return $query->orWhere('borrower_id', $borrowerId);
        }

        return $query;
    }
}
