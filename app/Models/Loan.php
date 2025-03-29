<?php

namespace App\Models;

use App\Casts\TimestampWithTimezoneCast;
use App\Events\LoanCompletedEvent;
use App\Transformers\LoanTransformer;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends BaseModel
{
    use SoftDeletes;

    protected $hidden = ["actual_pricing"];

    public static $rules = [
        "departure_at" => ["required"],
        "duration_in_minutes" => ["integer", "required", "min:30"],
        "estimated_distance" => ["integer", "required"],
        "estimated_insurance" => ["numeric", "required"],
        "estimated_price" => ["numeric", "required"],
        "platform_tip" => ["numeric", "present", "min:0"],
        "loanable_id" => "available",
        "message_for_owner" => ["present"],
        "reason" => ["present"],
    ];

    public static $transformer = LoanTransformer::class;

    public static $filterTypes = [
        "id" => "number",
        "actual_duration_in_minutes" => "number",
        "final_distance" => "number",
        "departure_at" => "date",
        "calendar_days" => "number",
        "loanable.type" => ["car", "bike", "trailer"],
        "loanable.owner.user.full_name" => "text",
        "borrower.user.full_name" => "text",
        "incidents.status" => ["in_process", "completed", "canceled"],
        "takeover.status" => ["in_process", "completed", "canceled"],
        "status" => ["in_process", "completed", "canceled"],
        "current_step" => [
            "intention",
            "pre_payment",
            "takeover",
            "handover",
            "payment",
            "extension",
            "incident",
        ],
        "community.name" => "text",
        "loanable.name" => "text",
    ];

    public static function boot()
    {
        parent::boot();

        // Update loan.status and loan.actual_return_at whenever an action is changed.
        foreach (
            [
                \App\Models\Extension::class,
                \App\Models\Handover::class,
                \App\Models\Incident::class,
                \App\Models\Intention::class,
                \App\Models\Payment::class,
                \App\Models\PrePayment::class,
                \App\Models\Takeover::class,
            ]
            as $class
        ) {
            $class::saved(function ($model) {
                $changed = false;

                $loan = $model->loan;

                $newStatus = $loan->getStatusFromActions();
                if ($newStatus != $loan->status) {
                    $loan->status = $newStatus;
                    $changed = true;
                    if ($loan->status === "completed") {
                        event(new LoanCompletedEvent($loan));
                    }
                }

                // Work with Carbon objects.
                $curReturnAt = $loan->actual_return_at
                    ? Carbon::parse($loan->actual_return_at)
                    : null;
                $newReturnAt = $loan->getActualReturnAtFromActions();

                if (!$curReturnAt || !$newReturnAt->equalTo($curReturnAt)) {
                    $loan->actual_return_at = $newReturnAt;
                    $changed = true;
                }

                if ($changed) {
                    $loan->save();
                }
            });

            $class::deleted(function ($model) {
                $changed = false;

                $loan = $model->loan;

                $newStatus = $loan->getStatusFromActions();
                if ($newStatus != $loan->status) {
                    $loan->status = $newStatus;
                    $changed = true;
                }

                // Work with Carbon objects.
                $curReturnAt = $loan->actual_return_at
                    ? Carbon::parse($loan->actual_return_at)
                    : null;
                $newReturnAt = $loan->getActualReturnAtFromActions();

                if (!$curReturnAt || !$newReturnAt->equalTo($curReturnAt)) {
                    $loan->actual_return_at = $newReturnAt;
                    $changed = true;
                }

                if ($changed) {
                    $loan->save();
                }
            });
        }

        Handover::saved(function (Handover $model) {
            if ($model->isContested()) {
                $model->loan->borrower_validated_at = null;
                $model->loan->owner_validated_at = null;
                $model->loan->save();
            }
        });

        Takeover::saved(function (Takeover $model) {
            if ($model->isContested()) {
                $model->loan->borrower_validated_at = null;
                $model->loan->owner_validated_at = null;
                $model->loan->save();
            }
        });

        // Update loan.status and loan.actual_return_at before saving
        self::saving(function ($loan) {
            if ($loan->canceled_at && "canceled" != $loan->status) {
                $loan->status = "canceled";
            } elseif (!$loan->canceled_at) {
                $loan->status = $loan->getStatusFromActions();
            }

            $loan->actual_return_at = $loan->getActualReturnAtFromActions();
        });
    }

    public static function getColumnsDefinition()
    {
        return [
            "*" => function ($query = null) {
                if (!$query) {
                    return "loans.*";
                }

                return $query->selectRaw("loans.*");
            },
            "calendar_days" => function ($query = null) {
                $calendarDaysSql = <<<SQL
EXTRACT(
    'day'
    FROM
        date_trunc('day', actual_return_at)
        - date_trunc('day', departure_at)
        + interval '1 day'
)::integer
SQL;

                if (!$query) {
                    return $calendarDaysSql;
                }

                return $query->selectRaw("$calendarDaysSql AS calendar_days");
            },

            // See comments in getActualDurationInMinutesAttribute
            "actual_duration_in_minutes" => function ($query = null) {
                $sql = <<<SQL
GREATEST(
    (    DATE_PART('day',    actual_return_at::timestamp - departure_at::timestamp) * 24
       + DATE_PART('hour',   actual_return_at::timestamp - departure_at::timestamp)) * 60 +
       + DATE_PART('minute', actual_return_at::timestamp - departure_at::timestamp),
    0)
SQL;
                if (!$query) {
                    return $sql;
                }

                return $query->selectRaw("$sql AS actual_duration_in_minutes");
            },

            "final_distance" => function ($query = null) {
                $sql = "GREATEST( handovers.mileage_end - takeovers.mileage_beginning, 0 )";
                if (!$query) {
                    return $sql;
                }
                $query->selectRaw("$sql AS final_distance");
                $query = static::addJoin(
                    $query,
                    "handovers",
                    "loans.id",
                    "=",
                    "handovers.loan_id"
                );
                $query = static::addJoin(
                    $query,
                    "takeovers",
                    "loans.id",
                    "=",
                    "takeovers.loan_id"
                );

                return $query;
            },

            "borrower_user_full_name" => function ($query = null) {
                if (!$query) {
                    return "CONCAT(borrower_users.name, ' ', borrower_users.last_name)";
                }

                $query->selectRaw(
                    "CONCAT(borrower_users.name, ' ', borrower_users.last_name)" .
                        " AS borrower_user_full_name"
                );

                $query = static::addJoin(
                    $query,
                    "borrowers",
                    "borrowers.id",
                    "=",
                    "loans.borrower_id"
                );

                $query = static::addJoin(
                    $query,
                    "users as borrower_users",
                    "borrower_users.id",
                    "=",
                    "borrowers.user_id"
                );

                return $query;
            },

            "loanable_owner_user_full_name" => function ($query = null) {
                if (!$query) {
                    return "CONCAT(owner_users.name, ' ', owner_users.last_name)";
                }

                $query->selectRaw(
                    "CONCAT(owner_users.name, ' ', owner_users.last_name)" .
                        " AS loanable_owner_user_full_name"
                );

                $query = static::addJoin(
                    $query,
                    "loanables as loanables_for_owner",
                    "loanables_for_owner.id",
                    "=",
                    "loans.loanable_id"
                );

                $query = static::addJoin(
                    $query,
                    "owners",
                    "owners.id",
                    "=",
                    "loanables_for_owner.owner_id"
                );

                $query = static::addJoin(
                    $query,
                    "users as owner_users",
                    "owner_users.id",
                    "=",
                    "owners.user_id"
                );

                return $query;
            },

            "community_name" => function ($query = null) {
                if (!$query) {
                    return "communities.name";
                }

                $query->selectRaw("communities.name AS community_name");

                $query = static::addJoin(
                    $query,
                    "communities",
                    "communities.id",
                    "=",
                    "loans.community_id"
                );

                return $query;
            },
        ];
    }

    // TODO: Move to a calendar helper (#1080).
    public static function getCalendarDays($start, $end)
    {
        // These variables are built gradually to become start and end of the
        // day as we move forward, hence their name.
        $startOfDaysCovered = $start->copy()->setMilliseconds(0);
        $endOfDaysCovered = $end->copy()->setMilliseconds(0);

        // Milliseconds must be set so the comparison is accurate.
        // Return 0 for degenerate loan intervals.
        if ($endOfDaysCovered->lessThanOrEqualTo($startOfDaysCovered)) {
            return 0;
        }

        // Snap to start of day.
        $startOfDaysCovered = $startOfDaysCovered
            ->setHours(0)
            ->setMinutes(0)
            ->setSeconds(0);

        // Snap to end of day (beginning of next day). Consider [, ) intervals.
        if (
            $endOfDaysCovered->hour > 0 ||
            $endOfDaysCovered->minute > 0 ||
            $endOfDaysCovered->second > 0
        ) {
            $endOfDaysCovered = $endOfDaysCovered
                ->addDays(1)
                ->setHours(0)
                ->setMinutes(0)
                ->setSeconds(0);
        }

        $days = $startOfDaysCovered->diffInDays($endOfDaysCovered, false);

        return $days;
    }

    public static function getRules($action = "", $auth = null)
    {
        $rules = parent::getRules($action, $auth);
        switch ($action) {
            case "create":
                $rules["community_id"] = "required";
                return $rules;
            default:
                return $rules;
        }
    }

    protected $casts = [
        "departure_at" => TimestampWithTimezoneCast::class,
        "canceled_at" => TimestampWithTimezoneCast::class,
        "actual_return_at" => TimestampWithTimezoneCast::class,
        "meta" => "array",
    ];

    protected $fillable = [
        "borrower_id",
        "canceled_at",
        "community_id",
        "departure_at",
        "duration_in_minutes",
        "estimated_distance",
        "estimated_insurance",
        "estimated_price",
        "loanable_id",
        "platform_tip",
        "message_for_owner",
        "reason",
    ];

    public $computed = [
        "actual_price",
        "actual_insurance",
        "actual_duration_in_minutes",
        "final_distance",
        "calendar_days",
        "is_contested",
        "total_actual_cost",
        "total_final_cost",
        "total_estimated_cost",
        "is_free",
        "needs_validation",
        "name",
    ];

    public $items = [
        "borrower",
        "community",
        "handover",
        "intention",
        "loanable",
        "payment",
        "pre_payment",
        "takeover",
        "car",
        "bike",
        "trailer",
    ];

    public $collections = ["actions", "extensions", "incidents", "expenses"];

    public function actions()
    {
        return $this->hasMany(Action::class)
            ->orderBy("weight", "asc")
            ->orderBy("id", "asc");
    }

    public function bike()
    {
        return $this->belongsTo(Bike::class, "loanable_id")->withTrashed();
    }

    public function borrower()
    {
        return $this->belongsTo(Borrower::class)->withTrashed();
    }

    public function car()
    {
        return $this->belongsTo(Car::class, "loanable_id")->withTrashed();
    }

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function extensions()
    {
        return $this->hasMany(Extension::class);
    }

    public function handover()
    {
        return $this->hasOne(Handover::class);
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }

    public function intention()
    {
        return $this->hasOne(Intention::class);
    }

    public function loanable()
    {
        return $this->belongsTo(Loanable::class)->withTrashed();
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function prePayment()
    {
        return $this->hasOne(PrePayment::class);
    }

    public function takeover()
    {
        return $this->hasOne(Takeover::class);
    }

    public function trailer()
    {
        return $this->belongsTo(Trailer::class, "loanable_id")->withTrashed();
    }
    
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function getActualDurationInMinutesAttribute()
    {
        $actualDurationInMinutes = (new Carbon(
            $this->departure_at
        ))->diffInMinutes(new Carbon($this->actual_return_at), false);

        // If the payment was made before the loan departure time, then return
        // a duration of 0. Negative durations would enable a borrower to earn cash!
        if ($actualDurationInMinutes < 0) {
            return 0;
        }

        return $actualDurationInMinutes;
    }
    
    public function getFinalDistanceAttribute()
    {
        if ($this->takeover && $this->handover && $this->handover->isCompleted()) {
            return $this->handover->mileage_end - $this->takeover->mileage_beginning;
        } else {
            return 0;
        }
    }

    public function getCalendarDaysAttribute()
    {
        return static::getCalendarDays(
            new Carbon($this->departure_at),
            new Carbon($this->actual_return_at)
        );
    }

    public function getIsFreeAttribute()
    {
        return $this->actual_price === 0 && $this->actual_insurance === 0;
    }

    public function getActualPricingAttribute()
    {
        $takeover = $this->takeover;
        $handover = $this->handover;

        $loanable = $this->getFullLoanable();

        if( $loanable->isPayingForLoans($this->borrower->user) ) {
            $pricing = $this->community->getPricingFor($loanable);
        } else {
            return 0;
        }

        if (!$pricing) {
            return 0;
        }
        $distance = $this->estimated_distance;
        if ($takeover && $handover && $handover->isCompleted()) {
            $distance = $handover->mileage_end - $takeover->mileage_beginning;
        }

        return $pricing->evaluateRule(
            $distance,
            $this->actual_duration_in_minutes,
            $loanable,
            $this
        );
    }

    public function getActualPriceAttribute()
    {
        $pricing = $this->actual_pricing;

        return max(0, is_array($pricing) ? $pricing[0] : $pricing);
    }

    public function getActualInsuranceAttribute()
    {
        $pricing = $this->actual_pricing;
        return is_array($pricing) ? max($pricing[1], 0) : 0;
    }

    public function getTotalActualCostAttribute()
    {
        $actual_expenses = $this->handover
            ? $this->handover->purchases_amount
            : 0;

        return round(
            $this->actual_price +
                $this->actual_insurance +
                $this->platform_tip -
                $actual_expenses,
            2
        );
    }

    public function getTotalFinalCostAttribute()
    {
        return $this->final_price +
            $this->final_insurance +
            $this->final_platform_tip -
            $this->final_purchases_amount;
    }

    public function getIsContestedAttribute(): bool
    {
        $takeover = $this->takeover;
        if ($takeover && $takeover->isContested()) {
            return true;
        }

        $handover = $this->handover;
        return $handover && $handover->isContested();
    }

    public function getTotalEstimatedCostAttribute()
    {
        return $this->estimated_price +
            $this->estimated_insurance +
            $this->platform_tip;
    }

    public function scopeIsPeriodUnavailable(
        Builder $query,
        $departureAt,
        $returnAt
    ) {
        $query
            ->where("status", "!=", "canceled")
            ->whereHas("intention", function ($q) {
                return $q->where("status", "=", "completed");
            })
            /*
                Intersection if: a1 > b0 and a0 < b1

                    a0           a1
                    [------------)
                          [------------)
                          b0           b1
            */
            ->where("actual_return_at", ">", $departureAt)
            ->where("departure_at", "<", $returnAt);
    }

    public function scopeAccessibleBy(Builder $query, $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isCommunityAdmin()) {
            return $query->where(function ($q) use ($user) {
                return $q->whereHas("community", function ($q) use ($user) {
                    $q->withAdminUser($user);
                });
            });
        }

        if ($user->owner) {
            $ownerId = $user->owner->id;
            $query = $query->whereHas("loanable", function ($q) use ($ownerId) {
                return $q->where("owner_id", $ownerId);
            });
        }

        if ($user->borrower) {
            $borrowerId = $user->borrower->id;
            $query = $query->orWhere("borrower_id", $borrowerId);
        }

        // Or belonging to its admin communities
        $query = $query->orWhere(function ($q) use ($user) {
            return $q->whereHas("community", function ($q) use ($user) {
                return $q->whereHas("users", function ($q) use ($user) {
                    return $q
                        ->where("community_user.user_id", $user->id);
                });
            });
        });

        return $query;
    }

    public function scopeCurrentStep(Builder $query, $step, $negative = false)
    {
        switch ($step) {
            case "pre_payment":
                $step = "prePayment";
            // no break (just rename the step and carry on)
            case "intention":
            case "takeover":
            case "handover":
            case "payment":
                return $query
                    ->whereHas($step, function ($q) {
                        return $q->where("status", "in_process");
                    })
                    ->whereDoesntHave("incidents", function ($q) {
                        return $q->where("status", "in_process");
                    })
                    ->whereDoesntHave("extensions", function ($q) {
                        return $q->where("status", "in_process");
                    });
            case "incident":
            case "extension":
                return $query->whereHas("{$step}s", function ($q) {
                    return $q->where("status", "in_process");
                });
            default:
                return $query;
        }
    }

    public function scopePrepaid(
        Builder $query,
        $value = true,
        $negative = false
    ) {
        // Negative case
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN) === $negative) {
            return $query->where(function ($q) {
                return $q
                    ->whereHas("prePayment", function ($q) {
                        return $q->where("status", "!=", "completed");
                    })
                    ->orWhereDoesntHave("prePayment");
            });
        }

        // Positive case
        return $query->whereHas("prePayment", function ($q) {
            return $q->where("status", "completed");
        });
    }

    public function scopeCompleted(
        Builder $query,
        $value = true,
        $negative = false
    ) {
        // Negative case
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN) === $negative) {
            return $query->where("status", "!=", "completed");
        }

        // Positive case
        return $query->where("status", "=", "completed");
    }

    public function scopeCanceled(
        Builder $query,
        $value = true,
        $negative = false
    ) {
        // Negative case
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN) === $negative) {
            return $query->where("status", "!=", "canceled");
        }

        // Positive case
        return $query->where("status", "=", "canceled");
    }

    /*
       This method checks whether this loan is in a state in which it can be
       canceled.
    */
    public function isCancelableBy(User $user)
    {
        if ($this->isCanceled()) {
            return false;
        }
        // Cannot cancel loans with transfer of funds
        if (
            (
                ($this->payment && $this->payment->status === "completed")
             || ($this->handover && $this->handover->status === "completed")
            )
            && $this->final_price > 0
        ) {
            return false;
        }

        // Admins can cancel any other time
        if (
            $user->isAdmin() ||
            $user->isCommunityAdmin() ||
            $user->isAdminOfCommunity($this->community->id)
        ) {
            return true;
        }

        // Unrelated users cannot cancel
        if (
            $this->borrower->user->id !== $user->id &&
            (!$this->loanable->owner ||
                $this->loanable->owner->user->id !== $user->id)
        ) {
            return false;
        }

        return // Free loans can be canceled at any time with no repercussions
            $this->is_free ||
                // If it's not free, then it can be canceled if the vehicle hasn't been taken
                (!$this->takeover || $this->takeover->status == "in_process") ||
                // or the reservation has not yet started (in case the takeover was completed too soon)
                CarbonImmutable::now()->isBefore(
                    CarbonImmutable::parse($this->departure_at)
                );
    }

    public function cancel($at = null)
    {
        $this->canceled_at = new Carbon($at);
        $this->status = "canceled";

        return $this;
    }

    public function isCanceled()
    {
        return $this->canceled_at || $this->status == "canceled";
    }

    public function getFullLoanable()
    {
        // Avoid loading the relation to this model, so the loanable is not duplicated
        // in the output. E.g. in the loanable and bike fields.
        switch ($this->loanable->type) {
            case "car":
                return Car::withTrashed()->find($this->loanable->id);
            case "bike":
                return Bike::withTrashed()->find($this->loanable->id);
            case "trailer":
                return Trailer::withTrashed()->find($this->loanable->id);
            default:
                return $this->loanable;
        }
    }

    /*
      This function is used to compute the status attribute of a loan and
      should be the single source of truth.

      Possible states:
        - in_process
        - canceled
        - completed

      Only accounts for actions, not any fields from the loan itself.
      Loan.canceled_at has precedence over this status.

      Refer to database/migrations/2022_04_12_144045_set_loan_status.php
      to see how older cases were accounted for.
    */
    public function getStatusFromActions()
    {
        // pre-payment step does not exist anymore

        // Handover
        $action = $this->handover;
        if ($action) {
            switch ($action->status) {
                case "in_process":
                case "completed":
                case "canceled":
                    return $action->status;
                    break;
                default:
                    throw new \Exception(
                        "Unexpected status for loan action: handover."
                    );
                    break;
            }
        }

        // payment step does not exist anymore

        // Intention
        $action = $this->intention;
        if ($action) {
            switch ($action->status) {
                case "canceled":
                    return "canceled";
                    break;
                case "in_process":
                case "completed":
                    return "in_process";
                    break;
                default:
                    throw new \Exception(
                        "Unexpected status for loan action: takeover."
                    );
                    break;
            }
        }

        return "in_process";
    }

    /*
      This function is used to compute the loan.actual_return_at attribute and
      should be the single source of truth.

      Acounts for loan duration_in_minutes, accepted extensions and early payments.

      Refer to database/migrations/2022_06_08_093323_set_actual_return_at.php
      to see how older cases were accounted for.
    */
    public function getActualReturnAtFromActions()
    {
        $departureAt = CarbonImmutable::parse($this->departure_at);

        $durationMinutes = $this->duration_in_minutes;

        // Account for the longest accepted extension
        foreach ($this->extensions as $extension) {
            if ("extension" == $extension->type && $extension->isCompleted()) {
                if ($extension->new_duration > $durationMinutes) {
                    $durationMinutes = $extension->new_duration;
                }
            }
        }

        $returnAt = $departureAt->addMinutes($durationMinutes);

        // Account for early payment.
        $payment = $this->payment;
        if ($payment && $payment->isCompleted()) {
            $paymentTime = CarbonImmutable::parse($payment->executed_at);
            if ($paymentTime->lessThan($returnAt)) {
                $returnAt = $paymentTime;
            }
        }

        return $returnAt;
    }

    public function hasBorrowerValidated(): bool
    {
        return !!$this->borrower_validated_at;
    }

    public function borrowerIsOwner(): bool
    {
        return $this->loanable->owner &&
            $this->borrower->user->is($this->loanable->owner->user);
    }

    public function hasOwnerValidated(): bool
    {
        return !!$this->owner_validated_at;
    }

    public function isFullyValidated(): bool
    {
        return $this->hasBorrowerValidated() && $this->hasOwnerValidated();
    }

    public function borrowerCanPay(): bool
    {
        return floatval($this->borrower->user->balance) >=
            $this->total_actual_cost;
    }

    public function getNeedsValidationAttribute(): bool
    {
        return $this->loanable->type === "car" &&
            !$this->is_free &&
            !$this->loanable->is_self_service &&
            $this->validationLimit()->isAfter(Carbon::now()) &&
            !$this->borrowerIsOwner();
    }

    public function validationLimit(): Carbon
    {
        return Carbon::parse($this->actual_return_at)->addHours(48);
    }

    public function getNameAttribute(): string
    {
        $borrower_name = $this->borrower->user ? $this->borrower->user->full_name : 'Inconnu·e';
        $name = $this->loanable->name." emprunté par ".$borrower_name." le ".Carbon::parse($this->departure_at)->isoFormat('LLLL');
        if( $this->reason ){
            $name .= " (".$this->reason.")";
        }
        return $name;
    }

    /**
     *  TODO(#1113) Move this logic to policy
     * @param User|null $user
     * @return bool
     */
    public function canBePaid(User $user = null): bool
    {
        if (!$this->payment || $this->payment->status !== "in_process") {
            return false;
        }

        if (!$this->takeover || !$this->takeover->isCompleted()) {
            return false;
        }

        if (!$this->handover || !$this->handover->isCompleted()) {
            return false;
        }

        if (!$this->borrowerCanPay()) {
            return false;
        }

        if ($user && $user->isAdmin()) {
            return true;
        }

        return !$this->needs_validation || $this->isFullyValidated();
    }


    /**
        This method actualy create loan and fuel expenses in the database
        for this loan.
    */
    public function writeExpenses() {
        // a loan should be completed before generating expenses
        if ($this->status !== "completed") return;

        // Update loan prices
        $this->final_price = $this->actual_price;
        $this->final_insurance = $this->actual_insurance;
        $this->final_platform_tip = $this->platform_tip;
        $this->final_purchases_amount = $this->handover->purchases_amount;
        $this->save();

        $distance = $this->handover->mileage_end - $this->takeover->mileage_beginning;
        $date = Carbon::parse($this->departure_at)->addMinutes($this->duration_in_minutes);
        $desc = $this->reason ? $this->reason. " - " : "";
        $desc .= $date->locale('fr_FR')->isoFormat('ddd Do MMMM');

        $loan_expense = new Expense;
        $loan_expense->name = "$desc ($distance km)";
        $loan_expense->amount = $this->final_price;
        $loan_expense->user_id = $this->borrower->user_id;
        $loan_expense->loanable_id = $this->loanable->id;
        $loan_expense->loan_id = $this->id;
        $loan_expense->type = 'debit'; // user will pay for this loan
        $loan_expense->executed_at = $date;

        $loan_tag = ExpenseTag::where('slug', 'loan');
        if( $loan_tag ) $loan_expense->tag()->associate($loan_tag->first());

        $loan_expense->save();

        if( $this->final_purchases_amount > 0 ) {
            $fuel_expense = new Expense;
            $fuel_expense->name = "";
            $fuel_expense->amount = floatval($this->final_purchases_amount);
            $fuel_expense->user_id = $this->borrower->user_id;
            $fuel_expense->loanable_id = $this->loanable->id;
            $fuel_expense->loan_id = $this->id;
            $fuel_expense->type = 'credit'; // user has already payed for this fuel
            $fuel_expense->executed_at = $date;

            $fuel_tag = ExpenseTag::where('slug', 'fuel');
            if( $fuel_tag ) $fuel_expense->tag()->associate($fuel_tag->first());

            $fuel_expense->save();
        }
    }
}
