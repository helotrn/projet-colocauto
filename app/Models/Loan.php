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
use App\Casts\TimestampWithTimezoneCast;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends BaseModel
{
    use SoftDeletes;

    public static $rules = [
        "departure_at" => ["required"],
        "duration_in_minutes" => ["integer", "required", "min:15"],
        "estimated_distance" => ["integer", "required"],
        "estimated_insurance" => ["numeric", "required"],
        "estimated_price" => ["numeric", "required"],
        "platform_tip" => ["numeric", "present", "min:0"],
        "loanable_id" => "available",
        "message_for_owner" => ["present"],
        "reason" => ["required"],
    ];

    public static $transformer = LoanTransformer::class;

    public static $filterTypes = [
        "id" => "number",
        "actual_duration_in_minutes" => "number",
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
extract(
    'day'
    from
        date_trunc('day', departure_at + duration_in_minutes * interval '1 minute')
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
	0,
	LEAST(
		COALESCE(loan_payment.duration_according_to_payment, 1000000000000),
		COALESCE(
			extension_max_duration.max_duration,
			loans.duration_in_minutes
		)
	)
)
SQL;
                if (!$query) {
                    return $sql;
                }

                if (
                    false === strpos($query->toSql(), "extension_max_duration")
                ) {
                    $query
                        ->selectRaw("$sql AS actual_duration_in_minutes")
                        ->leftJoinSub(
                            <<<SQL
SELECT
    max(new_duration) AS max_duration,
    loan_id
FROM extensions
WHERE status = 'completed'
GROUP BY loan_id
SQL
                            ,
                            "extension_max_duration",
                            "extension_max_duration.loan_id",
                            "=",
                            "loans.id"
                        )
                        ->leftJoinSub(
                            <<<SQL
SELECT
    DATE_PART('day', payments.executed_at::timestamp - l.departure_at::timestamp) * 24 +
   DATE_PART('hour', payments.executed_at::timestamp - l.departure_at::timestamp) * 60 +
   DATE_PART('minute', payments.executed_at::timestamp - l.departure_at::timestamp) AS duration_according_to_payment,
    payments.loan_id
FROM payments
INNER JOIN loans l ON l.id = payments.loan_id
WHERE payments.status = 'completed'
SQL
                            ,
                            "loan_payment",
                            "loan_payment.loan_id",
                            "=",
                            "loans.id"
                        );
                }

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

    public static function getCalendarDays($start, $end)
    {
        if ($end->dayOfYear === $start->dayOfYear) {
            return 1;
        }

        if ($end->dayOfYear < $start->dayOfYear) {
            return 1 +
                (365 + $start->format("L") + $end->dayOfYear) -
                $start->dayOfYear;
        }

        return 1 + $end->dayOfYear - $start->dayOfYear;
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
        "calendar_days",
        "is_contested",
        "total_actual_cost",
        "total_final_cost",
        "total_estimated_cost",
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
    ];

    public $collections = ["actions", "extensions", "incidents"];

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
        return $this->belongsTo(Borrower::class);
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

    public function getActualDurationInMinutesAttribute()
    {
        if (isset($this->attributes["actual_duration_in_minutes"])) {
            return $this->attributes["actual_duration_in_minutes"];
        }

        // Initial duration
        $durationInMinutes = $this->duration_in_minutes;

        // Account for the longest approved duration
        $completedExtensions = $this->extensions->where("status", "completed");
        if (!$completedExtensions->isEmpty()) {
            $durationInMinutes = $completedExtensions->reduce(function (
                $acc,
                $ext
            ) {
                if ($ext->new_duration > $acc) {
                    return $ext->new_duration;
                }
                return $acc;
            },
            $this->duration_in_minutes);
        }

        // If payment is completed, then account for early termination
        if ($this->payment && $this->payment->isCompleted()) {
            // diffInMinutes:
            //   - All values are truncated and not rounded
            //   - Takes, as 2nd argument, an absolute boolean option (true by
            //     default) that make the method return an absolute value no
            //     matter which date is greater than the other.
            // Notice the order of instances: A->diff(B) means B - A.
            // From: https://carbon.nesbot.com/docs/
            $diffPaymentAndDeparture = (new Carbon(
                $this->departure_at
            ))->diffInMinutes(new Carbon($this->payment->executed_at), false);

            return min(
                // If payment was executed before departure, diffPaymentAndDeparture < 0, then set duration = 0.
                // Otherwise, account for early return (payment).
                max($diffPaymentAndDeparture, 0),
                $durationInMinutes
            );
        }

        return $durationInMinutes;
    }

    public function getCalendarDaysAttribute()
    {
        if (isset($this->attributes["calendar_days"])) {
            return $this->attributes["calendar_days"];
        }

        $start = new Carbon($this->departure_at);
        $end = $start
            ->copy()
            ->add($this->actual_duration_in_minutes, "minutes");

        return static::getCalendarDays($start, $end);
    }

    public function getActualPriceAttribute()
    {
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

    public function getActualInsuranceAttribute()
    {
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
        if($takeover && $takeover->isContested()){
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

    public function scopeAccessibleBy(Builder $query, $user)
    {
        if ($user->isAdmin()) {
            return $query;
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
                        ->where("community_user.user_id", $user->id)
                        ->where("community_user.role", "admin");
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

    public function scopeDepartureInLessThan(
        Builder $query,
        $amount,
        $unit = "minutes"
    ) {
        if (
            !in_array($unit, [
                "minute",
                "minutes",
                "hour",
                "hours",
                "day",
                "days",
            ])
        ) {
            throw new \Exception("invalid unit");
        }

        return $query
            ->whereRaw("(departure_at - $amount * interval '1 $unit') < ?", [
                Carbon::now(),
            ])
            ->whereRaw("departure_at > now()");
    }

    /*
       This method checks whether this loan is in a state in which it can be
       canceled.
    */
    public function isCancelable()
    {
        if ($this->isCanceled()) {
            return false;
        }

        foreach ($this->actions as $action) {
            if (
                // If takeover exists, then it must be in process.
                ("takeover" == $action->type &&
                    "in_process" != $action->status) ||
                // If handover or payment exist, then loan can't be canceled.
                "handover" == $action->type ||
                "payment" == $action->type
            ) {
                return false;
            }
        }

        return true;
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
        switch ($this->loanable->type) {
            case "car":
                return $this->car;
            case "bike":
                return $this->bike;
            case "trailer":
                return $this->trailer;
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
        // Loan is canceled if pre-payment is canceled.
        $action = $this->prePayment;
        if ($action) {
            switch ($action->status) {
                case "canceled":
                    return "canceled";
                    break;
            }
        }

        // Payment
        $action = $this->payment;
        if ($action) {
            switch ($action->status) {
                case "in_process":
                case "completed":
                    return $action->status;
                    break;
                default:
                    throw new \Exception(
                        "Unexpected status for loan action: payment."
                    );
                    break;
            }
        }

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
}
