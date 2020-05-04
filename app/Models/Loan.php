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
use App\Utils\TimestampWithTimezone;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vkovic\LaravelCustomCasts\HasCustomCasts;

class Loan extends BaseModel
{
    use HasCustomCasts, SoftDeletes;

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
        'estimated_insurance' => [
            'numeric',
            'required',
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
                if (!$query) {
                    return '(array_agg(all_actions.status ORDER BY all_actions.id DESC))[1] ';
                }

                $query = $query->leftJoin('actions AS all_actions', function ($j) {
                    return $j->on('all_actions.loan_id', '=', 'loans.id')
                        ->whereNotIn('type', ['extension', 'incident']);
                });

                return $query
                    ->selectRaw(
                        '(array_agg(all_actions.status ORDER BY all_actions.id DESC))[1] AS status'
                    )->groupBy('loans.id');
            }
        ];
    }

    public static function getCalendarDays($start, $end) {
        if ($end->dayOfYear === $start->dayOfYear) {
            return 1;
        }

        if ($end->dayOfYear < $start->dayOfYear) {
            return 1 + ((365 + $start->format('L')) + $end->dayOfYear) - $start->dayOfYear;
        }

        return 1 + $end->dayOfYear - $start->dayOfYear;
    }

    protected $casts = [
        'departure_at' => TimestampWithTimezone::class,
    ];

    protected $fillable = [
        'departure_at',
        'duration_in_minutes',
        'estimated_distance',
        'estimated_insurance',
        'estimated_price',
        'message_for_owner',
        'reason',
    ];

    public $computed = [
      'actual_price',
      'actual_duration_in_minutes',
      'calendar_days',
      'canceled_at',
    ];

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

    public $collections = ['actions', 'extensions', 'incidents'];

    public function actions() {
        return $this->hasMany(Action::class)->orderBy('weight', 'asc')->orderBy('id', 'asc');
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

    public function getCalendarDaysAttribute() {
        $start = new Carbon($this->departure_at);
        $end = $start->copy()->add($this->actual_duration_in_minutes, 'minutes');

        return static::getCalendarDays($start, $end);
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

        $values = $pricing->evaluateRule(
            $handover->mileage_end - $takeover->mileage_beginning,
            $this->actual_duration_in_minutes,
            $this->loanable,
            $this
        );

        return is_array($values) ? $values[0] : $values;
    }

    public function getActualInsuranceAttribute() {
        $takeover = $this->takeover;
        $handover = $this->handover;

        if (!$takeover || !$handover) {
            return null;
        }

        if (!$takeover->executed_at || !$handover->executed_at) {
            return null;
        }

        $pricing = $this->community->getPricingFor($this->loanable);

        $values = $pricing->evaluateRule(
            $handover->mileage_end - $takeover->mileage_beginning,
            $this->actual_duration_in_minutes,
            $this->loanable,
            $this
        );

        return is_array($values) ? $values[0] : $values;
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
