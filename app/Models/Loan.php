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
        'platform_tip' => [
            'numeric',
            'present',
            'min:0',
        ],
        'loanable_id' => 'available',
        'message_for_owner' => [ 'present' ],
        'reason' => [ 'required' ],
    ];

    public static $transformer = LoanTransformer::class;

    public static $filterTypes = [
        'departure_at' => 'date',
        'calendar_days' => 'number',
        'loanable.type' => ['car', 'bike', 'trailer'],
        'loanable.owner.user.full_name' => 'text',
        'borrower.user.full_name' => 'text',
        'incidents.status' => ['in_process', 'completed', 'canceled'],
        'takeover.status' => ['in_process', 'completed', 'canceled'],
        'loan_status' => ['in_process', 'completed', 'canceled'],
        'current_step' => [
            'intention',
            'pre_payment',
            'takeover',
            'handover',
            'payment',
            'extension',
            'incident'
        ],
        'community.id' => [
            'type' => 'relation',
            'query' => [
                'slug' => 'communities',
                'value' => 'id',
                'text' => 'name',
                'params' => [
                    'fields' => 'id,name',
                ],
            ],
        ],
        'loanable.id' => [
            'type' => 'relation',
            'query' => [
                'slug' => 'loanables',
                'value' => 'id',
                'text' => 'name',
                'params' => [
                    'fields' => 'id,name',
                ],
            ],
        ],
    ];

    public static function boot() {
        parent::boot();

        self::saved(function ($model) {
            if ($model->loanable
                // Check existence on database because the model is
                // not updated automatically in the request lifecycle
                && !$model->intention()->first()) {
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
            'calendar_days' => function ($query = null) {
                $calendarDaysSql = <<<SQL
extract(
    'day'
    from
        date_trunc('day', departure_at + duration_in_minutes * interval '1 minute')
        - date_trunc('day', departure_at)
        + interval '1 day'
)::integer
SQL
                ;

                if (!$query) {
                    return $calendarDaysSql;
                }

                return $query->selectRaw("$calendarDaysSql AS calendar_days");
            },
            'loan_status' => function ($query = null) {
                $loanStatusSql = '(array_agg(all_actions.status ORDER BY all_actions.id DESC))[1]';
                if (!$query) {
                    return $loanStatusSql;
                }

                $query = static::addJoin($query, 'actions AS all_actions', function ($j) {
                    return $j->on('all_actions.loan_id', '=', 'loans.id')
                        ->whereNotIn('all_actions.type', ['extension', 'incident']);
                });

                return $query
                    ->selectRaw("$loanStatusSql AS loan_status")
                    ->groupBy('loans.id');
            },
            'actual_duration_in_minutes' => function ($query = null) {
                if (!$query) {
                    return 'max(all_extensions.new_duration)';
                }

                $query = static::addJoin($query, 'extensions AS all_extensions', function ($j) {
                    return $j->on('all_extensions.loan_id', '=', 'loans.id')
                        ->where('all_extensions.status', 'completed');
                });

                return $query
                    ->selectRaw("max(all_extensions.new_duration) AS actual_duration_in_minutes");
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
        'borrower_id',
        'departure_at',
        'duration_in_minutes',
        'estimated_distance',
        'estimated_insurance',
        'estimated_price',
        'loanable_id',
        'platform_tip',
        'message_for_owner',
        'reason',
    ];

    public $computed = [
      'actual_price',
      'actual_insurance',
      'actual_duration_in_minutes',
      'calendar_days',
      'canceled_at',
      'total_final_cost',
      'total_estimated_cost',
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

    public function bike() {
        return $this->belongsTo(Bike::class, 'loanable_id');
    }

    public function borrower() {
        return $this->belongsTo(Borrower::class);
    }

    public function car() {
        return $this->belongsTo(Car::class, 'loanable_id');
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

    public function trailer() {
        return $this->belongsTo(Trailer::class, 'loanable_id');
    }

    public function getActualDurationInMinutesAttribute() {
        if (isset($this->attributes['actual_duration_in_minutes'])) {
            return $this->attributes['actual_duration_in_minutes'];
        }

        $completedExtensions = $this->extensions->where('status', 'completed');
        if (!$completedExtensions->isEmpty()) {
            return $completedExtensions->reduce(function ($acc, $ext) {
                if ($ext->new_duration > $acc) {
                    return $ext->new_duration;
                }
            }, $this->duration_in_minutes);
        }

        return $this->duration_in_minutes;
    }

    public function getCalendarDaysAttribute() {
        if (isset($this->attributes['calendar_days'])) {
            return $this->attributes['calendar_days'];
        }

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

        $loanable = $this->getFullLoanable();

        $pricing = $this->community->getPricingFor($loanable);

        if (!$pricing) {
            return 0;
        }

        $values = $pricing->evaluateRule(
            $handover->mileage_end - $takeover->mileage_beginning,
            $this->actual_duration_in_minutes,
            $loanable,
            $this
        );

        return max(0, is_array($values) ? $values[0] : $values);
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

        $loanable = $this->getFullLoanable();

        $pricing = $this->community->getPricingFor($loanable);

        if (!$pricing) {
            return 0;
        }

        $values = $pricing->evaluateRule(
            $handover->mileage_end - $takeover->mileage_beginning,
            $this->actual_duration_in_minutes,
            $loanable,
            $this
        );

        return max(0, is_array($values) ? $values[1] : $values);
    }

    public function getTotalActualCostAttribute() {
        return $this->actual_price + $this->actual_insurance + $this->platform_tip;
    }

    public function getTotalFinalCostAttribute() {
        return $this->final_price + $this->final_insurance + $this->final_platform_tip;
    }

    public function getCanceledAtAttribute() {
        $canceledAction = $this->actions->where('status', 'canceled')->first();

        if ($canceledAction) {
            return $canceledAction->executed_at;
        }

        return null;
    }

    public function getTotalEstimatedCostAttribute() {
        return $this->estimated_price + $this->estimated_insurance + $this->platform_tip;
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
            $query = $query->orWhere('borrower_id', $borrowerId);
        }


        // Or belonging to its admin communities
        $query = $query->orWhere(function ($q) use ($user) {
            return $q->whereHas('community', function ($q) use ($user) {
                return $q->whereHas('users', function ($q) use ($user) {
                    return $q->where('community_user.user_id', $user->id)
                        ->where('community_user.role', 'admin');
                });
            });
        });

        return $query;
    }

    public function scopeCurrentStep(Builder $query, $step, $negative = false) {
        switch ($step) {
            case 'pre_payment':
                $step = 'prePayment';
                // no break (just rename the step and carry on)
            case 'intention':
            case 'takeover':
            case 'handover':
            case 'payment':
                return $query
                    ->whereHas($step, function ($q) {
                        return $q->where('status', 'in_process');
                    })
                    ->whereDoesntHave('incidents', function ($q) {
                        return $q->where('status', 'in_process');
                    })
                    ->whereDoesntHave('extensions', function ($q) {
                        return $q->where('status', 'in_process');
                    });
            case 'incident':
            case 'extension':
                return $query
                    ->whereHas("{$step}s", function ($q) {
                        return $q->where('status', 'in_process');
                    });
            default:
                return $query;
        }
    }

    protected function getFullLoanable() {
        switch ($this->loanable->type) {
            case 'car':
                return $this->car;
            case 'bike':
                return $this->bike;
            case 'trailer':
                return $this->trailer;
            default:
                return $this->loanable;
        }
    }
}
