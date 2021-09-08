<?php

namespace App\Models;

use App\Models\Community;
use App\Models\Loan;
use App\Models\Owner;
use App\Models\User;
use App\Transformers\LoanableTransformer;
use App\Casts\PointCast;
use Carbon\Carbon;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Eluceo\iCal\Component\Timezone;
use Eluceo\iCal\Component\TimezoneRule;
use Eluceo\iCal\Property\Event\RecurrenceRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use MStaack\LaravelPostgis\Eloquent\PostgisTrait;

class Loanable extends BaseModel
{
    use PostgisTrait, SoftDeletes;

    public $readOnly = true;

    public static $transformer = LoanableTransformer::class;

    public static $filterTypes = [
        "id" => "number",
        "name" => "text",
        "type" => ["bike", "car", "trailer"],
        "deleted_at" => "date",
        "is_deleted" => "boolean",
    ];

    public static $rules = [
        "comments" => ["present"],
        "instructions" => ["present"],
        "location_description" => ["present"],
        "name" => ["required"],
        "position" => ["required"],
        "type" => ["required", "in:car,bike,trailer"],
    ];

    public static $sizes = [
        "thumbnail" => "256x@fit",
    ];

    public static function getRules($action = "", $auth = null)
    {
        if ($action === "update") {
            return array_diff_key(static::$rules, ["type" => false]);
        }

        return parent::getRules($action, $auth);
    }

    public static function boot()
    {
        parent::boot();

        self::deleted(function ($model) {
            $model
                ->loans()
                ->completed(false)
                ->delete();
        });

        self::restored(function ($model) {
            $model->loans()->restore();
        });

        self::saved(function ($model) {
            if (!$model->created_at) {
                // Most likely deleting: skipping
                return $model;
            }

            $calendar = new Calendar(
                "locomotion.app/api/loanables/{$model->id}.ics"
            );

            $baseEvent = new Event();

            $tz = $model::buildTimezone();
            $dtz = new \DateTimeZone("America/Montreal");
            $calendar->setTimezone($tz);

            $byDays = ["SU", "MO", "TU", "WE", "TH", "FR", "SA"];

            switch ($model->availability_mode) {
                case "always":
                    $exceptions = json_decode($model->availability_json) ?: [];

                    foreach ($exceptions as $exception) {
                        switch ($exception->type) {
                            case "dates":
                                static::addDates($model, $exception, $calendar);
                                break;
                            default:
                                break;
                        }
                    }
                    break;
                case "never":
                default:
                    $baseEvent
                        ->setDtStart(
                            new \DateTime($model->created_at->format("Y-m-d"))
                        )
                        ->setDtEnd(
                            new \DateTime($model->created_at->format("Y-m-d"))
                        )
                        ->setNoTime(true);

                    $recurrence = new RecurrenceRule();
                    $recurrence->setFreq(RecurrenceRule::FREQ_MONTHLY);

                    $exceptions = json_decode($model->availability_json) ?: [];

                    foreach ($exceptions as $exception) {
                        switch ($exception->type) {
                            case "dates":
                                static::addDatesException(
                                    $baseEvent,
                                    $exception,
                                    $calendar
                                );
                                break;
                            case "weekdays":
                                $byDays = array_diff(
                                    $byDays,
                                    $exception->scope
                                );

                                if ($exception->period !== "00:00-23:59") {
                                    static::addWeekdaysExceptionScope(
                                        $model,
                                        $exception,
                                        $calendar
                                    );
                                }
                                break;
                        }
                    }

                    if (!empty($byDays)) {
                        $recurrence->setByDay(join(",", $byDays));
                    }
                    $baseEvent->setRecurrenceRule($recurrence);
                    $calendar->addComponent($baseEvent);
                    break;
            }

            return static::withoutEvents(function () use ($model, $calendar) {
                $model->availability_ics = $calendar->render();
                $model->save();
            });
        });
    }

    public static function getColumnsDefinition()
    {
        return [
            "*" => function ($query = null) {
                if (!$query) {
                    return "loanables.*";
                }

                return $query->selectRaw("loanables.*");
            },

            "owner_user_full_name" => function ($query = null) {
                if (!$query) {
                    return "CONCAT(owner_users.name, ' ', owner_users.last_name)";
                }

                $query->selectRaw(
                    "CONCAT(owner_users.name, ' ', owner_users.last_name)" .
                        " AS owner_user_full_name"
                );

                $query = static::addJoin(
                    $query,
                    "owners",
                    "owners.id",
                    "=",
                    "loanables.owner_id"
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
        ];
    }

    protected $hidden = ["availability_ics"];

    protected $postgisFields = ["position"];

    protected $postgisTypes = [
        "position" => [
            "geomtype" => "geography",
        ],
    ];

    protected $casts = [
        "position" => PointCast::class,
    ];

    protected $with = ["image"];

    public $computed = [
        "car_insurer",
        "events",
        "has_padlock",
        "position_google",
    ];

    public $items = ["owner", "community", "padlock"];

    public $morphOnes = [
        "image" => "imageable",
    ];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function image()
    {
        return $this->hasOne(Image::class, "imageable_id")
            ->where("field", "image")
            ->whereIn("imageable_type", [
                "App\Models\Bike",
                "App\Models\Car",
                "App\Models\Loanable",
                "App\Models\Trailer",
            ]);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function padlock()
    {
        return $this->hasOne(Padlock::class, "loanable_id");
    }

    public $collections = ["loans"];

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function isAvailable(
        $departureAt,
        $durationInMinutes,
        $ignoreLoanIds = []
    ) {
        $ical = new \ICal\ICal(
            [0 => [$this->availability_ics]],
            [
                "defaultTimeZone" => "America/Toronto",
                "defaultWeekStart" => "SU",
                "filterDaysBefore" => 1,
                "filterDaysAfter" => 365,
            ]
        );

        if (!is_a(\Carbon\Carbon::class, $departureAt)) {
            $departureAt = new \Carbon\Carbon($departureAt);
        }

        $returnAt = $departureAt->copy()->add($durationInMinutes, "minutes");

        $events = $ical->eventsFromRange($departureAt, $returnAt);

        if (!empty($events)) {
            return false;
        }

        $query = Loan::where("loanable_id", $this->id);

        if ($ignoreLoanIds) {
            $query = $query->whereNotIn("loans.id", $ignoreLoanIds);
        }

        $cDef = Loan::getColumnsDefinition();
        $query = $cDef["*"]($query);
        $query = $cDef["loan_status"]($query);
        $query = $cDef["actual_duration_in_minutes"]($query);

        $query
            ->where(\DB::raw($cDef["loan_status"]()), "!=", "canceled")
            ->whereHas("intention", function ($q) {
                return $q->where("status", "=", "completed");
            })
            ->whereRaw(
                "(departure_at + " .
                    "COALESCE({$cDef["actual_duration_in_minutes"]()}, duration_in_minutes) " .
                    "* interval '1 minute') > ?",
                [$departureAt]
            )
            ->where("departure_at", "<", $returnAt)
            ->where("loanable_id", $this->id);

        return $query->get()->count() === 0;
    }

    public function getCommunityForLoanBy(User $user): ?Community
    {
        // The reference community for a loan is...
        // 1. The community of the loanable, if the user is an approved member as well
        if ($this->community) {
            if ($user->approvedCommunities->find($this->community->id)) {
                return $this->community;
            }

            // 2. A children community that the user is a member of as well
            // It is assumed that there is only one since children communities are exclusive area
            if (
                $community = $this->community->children
                    ->whereIn("id", $user->approvedCommunities->pluck("id"))
                    ->first()
            ) {
                return $community;
            }
        }

        // 3. The community of the loanable owner that is common with the user
        // It is assumed that there is one, otherwise the user wouldn't
        // have access to that loanable at this point
        $communityId = current(
            array_intersect(
                $user->getAccessibleCommunityIds()->toArray(),
                $this->owner->user->getAccessibleCommunityIds()->toArray()
            )
        );
        $community = Community::where("id", $communityId)->first();
        return $community;
    }

    public function getEventsAttribute()
    {
        try {
            $ical = new \ICal\ICal(
                [0 => [$this->availability_ics]],
                [
                    "defaultTimeZone" => "America/Toronto",
                    "defaultWeekStart" => "SU",
                    "filterDaysBefore" => 1,
                    "filterDaysAfter" => 365,
                ]
            );

            $events = [];
            foreach ($ical->events() as $event) {
                $startDate = new Carbon($event->dtstart);
                $endDate = new Carbon($event->dtend);
                $period =
                    $startDate->format("H:i") . "-" . $endDate->format("H:i");

                $fullDay = $period === "00:00-00:00";

                $events[] = $fullDay
                    ? [
                        "start" => $startDate->format("Y-m-d"),
                        "end" => $endDate->format("Y-m-d"),
                        "period" =>
                            $startDate->format("H:i") .
                            "-" .
                            $endDate->format("H:i"),
                    ]
                    : [
                        "start" => $startDate->format("Y-m-d H:i"),
                        "end" => $endDate->format("Y-m-d H:i"),
                        "period" =>
                            $startDate->format("H:i") .
                            "-" .
                            $endDate->format("H:i"),
                    ];
            }
            return $events;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getHasPadlockAttribute()
    {
        return !!$this->padlock;
    }

    public function getCarInsurerAttribute()
    {
        if ($this->type === "car") {
            return Car::find($this->id)->insurer;
        }

        return null;
    }

    public function getPositionGoogleAttribute()
    {
        return [
            "lat" => $this->position[0],
            "lng" => $this->position[1],
        ];
    }

    public function scopeWithDeleted(Builder $query, $value, $negative = false)
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN) !== $negative) {
            return $query->withTrashed();
        }

        return $query;
    }

    public function scopeIsDeleted(Builder $query, $value, $negative = false)
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN) !== $negative) {
            return $query
                ->withTrashed()
                ->where("{$this->getTable()}.deleted_at", "!=", null);
        }

        return $query;
    }

    public function scopeAccessibleBy(Builder $query, $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        $allowedTypes = ["bike", "trailer"];
        if ($user->borrower && $user->borrower->validated) {
            $allowedTypes[] = "car";
        }

        $query = $query
            // A user has access to...
            ->where(function ($q) use ($user, $allowedTypes) {
                // Communities that you directly belong to
                $approvedCommunities = $user->approvedCommunities;

                // Communities and parents, recursively.
                $communityIds = collect();
                foreach ($approvedCommunities as $community) {
                    while ($community) {
                        // Break the loop id community is already there.
                        if ($communityIds->contains($community->id)) {
                            break;
                        }

                        $communityIds->push($community->id);

                        // Does this community have a parent?
                        $community = $community->parent;
                    }
                }

                if ($communityIds->count() === 0) {
                    $communityIds->push(0);
                }

                $q = $q->where(function ($q) use ($communityIds) {
                    return $q
                        // ...loanables belonging to its accessible communities...
                        ->whereHas("community", function ($q) use (
                            $communityIds
                        ) {
                            return $q->whereIn("communities.id", $communityIds);
                        })
                        // ...or belonging to children communities that allow sharing with
                        // parent communities (share_with_parent_communities = true)
                        ->orWhereHas("community", function ($q) use (
                            $communityIds
                        ) {
                            $childrenIds = Community::childOf(
                                $communityIds->toArray()
                            )->pluck("id");
                            return $q
                                ->whereIn("communities.id", $childrenIds)
                                ->where("share_with_parent_communities", true);
                        })
                        // ...or belonging to owners of his accessible communities
                        // that do not have a community specified directly
                        // (communities through user through owner)
                        ->orWhere(function ($q) use ($communityIds) {
                            return $q
                                ->whereHas("owner", function ($q) use (
                                    $communityIds
                                ) {
                                    return $q->whereHas("user", function (
                                        $q
                                    ) use ($communityIds) {
                                        // (direct community)
                                        return $q
                                            ->whereHas("communities", function (
                                                $q
                                            ) use ($communityIds) {
                                                return $q
                                                    ->whereIn(
                                                        "community_user.community_id",
                                                        $communityIds
                                                    )
                                                    ->whereNotNull(
                                                        "community_user.approved_at"
                                                    )
                                                    ->whereNull(
                                                        "community_user.suspended_at"
                                                    );
                                            })
                                            // (child community if shared with parent community)
                                            ->orWhereHas(
                                                "communities",
                                                function ($q) use (
                                                    $communityIds
                                                ) {
                                                    $childrenIds = Community::childOf(
                                                        $communityIds->toArray()
                                                    )->pluck("id");
                                                    return $q
                                                        ->whereIn(
                                                            "communities.id",
                                                            $childrenIds
                                                        )
                                                        ->where(
                                                            "share_with_parent_communities",
                                                            true
                                                        );
                                                }
                                            )
                                            // (parent community downward)
                                            ->orWhereHas(
                                                "communities",
                                                function ($q) use (
                                                    $communityIds
                                                ) {
                                                    $parentIds = Community::parentOf(
                                                        $communityIds->toArray()
                                                    )->pluck("id");
                                                    return $q->whereIn(
                                                        "communities.id",
                                                        $parentIds
                                                    );
                                                }
                                            );
                                    });
                                })
                                ->whereDoesntHave("community");
                        });
                });

                // ...and cars are only allowed if the borrower profile is approved
                switch (get_class($this)) {
                    case "App\Models\Bike":
                    case "App\Models\Trailer":
                        break;
                    case "App\Models\Car":
                        if (!in_array("car", $allowedTypes)) {
                            return $q->whereRaw("1=0");
                        }
                        break;
                    default:
                        return $q->whereIn("type", $allowedTypes);
                }
            });

        if ($user->owner) {
            // ...and his/her own cars even if the borrower profile is not approved
            $query = $query->orWhere(function ($q) use ($user) {
                return $q->whereHas("owner", function ($q) use ($user) {
                    return $q->where("owners.id", $user->owner->id);
                });
            });
        }

        return $query;
    }

    public function scopeSearch(Builder $query, $q)
    {
        if (!$q) {
            return $query;
        }

        $table = $this->getTable();
        return $query->where(
            \DB::raw("unaccent($table.name)"),
            "ILIKE",
            \DB::raw("unaccent('%$q%')")
        );
    }

    protected static function buildTimezone()
    {
        $tzName = "America/Montreal";
        $dtz = new \DateTimeZone($tzName);
        date_default_timezone_set($tzName);

        $tzRuleDst = new TimezoneRule(TimezoneRule::TYPE_DAYLIGHT);
        $tzRuleDst->setTzName("EDT");
        $tzRuleDst->setDtStart(new \DateTime("2007-11-04 02:00:00", $dtz));
        $tzRuleDst->setTzOffsetFrom("-0400");
        $tzRuleDst->setTzOffsetTo("-0500");

        $dstRecurrenceRule = new RecurrenceRule();
        $dstRecurrenceRule->setFreq(RecurrenceRule::FREQ_YEARLY);
        $dstRecurrenceRule->setByMonth(3);
        $dstRecurrenceRule->setByDay("2SU");
        $tzRuleDst->setRecurrenceRule($dstRecurrenceRule);

        $tzRuleStd = new TimezoneRule(TimezoneRule::TYPE_STANDARD);
        $tzRuleStd->setTzName("EST");
        $tzRuleStd->setDtStart(new \DateTime("2007-03-11 02:00:00", $dtz));
        $tzRuleStd->setTzOffsetFrom("-0500");
        $tzRuleStd->setTzOffsetTo("-0400");

        $stdRecurrenceRule = new RecurrenceRule();
        $stdRecurrenceRule->setFreq(RecurrenceRule::FREQ_YEARLY);
        $stdRecurrenceRule->setByMonth(10);
        $stdRecurrenceRule->setByDay("2SU");
        $tzRuleStd->setRecurrenceRule($stdRecurrenceRule);

        $tz = new Timezone($tzName);
        $tz->addComponent($tzRuleDst);
        $tz->addComponent($tzRuleStd);

        return $tz;
    }

    protected static function getFirstDateOnA($day, $model)
    {
        $date = new Carbon($model->created_at);
        $days = [
            "SU" => "sunday",
            "MO" => "monday",
            "TU" => "tuesday",
            "WE" => "wednesday",
            "TH" => "thursday",
            "FR" => "friday",
            "SA" => "saturday",
        ];
        $strDay = $days[$day];
        return $date->modify("next $strDay");
    }

    protected static function getPeriodLimits($baseDate, $startTime, $endTime)
    {
        return [
            $baseDate->copy()->setTime(0, 0, 0),
            $baseDate->copy()->setTime($startTime[0], $startTime[1], 0),
            $baseDate->copy()->setTime($endTime[0], $endTime[1], 0),
            $baseDate->copy()->setTime(23, 59, 59),
        ];
    }

    protected static function addDatesException(
        &$baseEvent,
        &$exception,
        &$calendar
    ) {
        foreach ($exception->scope as $date) {
            switch ($exception->available) {
                case false:
                    $baseDate = new Carbon(
                        $date,
                        new \DateTimeZone("America/Montreal")
                    );
                    switch ($exception->period) {
                        case "00:00-23:59":
                            $event = new Event();
                            $event
                                ->setDtStart($baseDate)
                                ->setDtEnd($baseDate)
                                ->setNoTime(true);
                            $calendar->addComponent($event);
                            break;
                        default:
                            $startDayEvent = new Event();
                            $endDayEvent = new Event();

                            [$startTime, $endTime] = explode(
                                "-",
                                $exception->period
                            );
                            $startTime = explode(":", $startTime);
                            $endTime = explode(":", $endTime);

                            [
                                $startOfDay,
                                $startOfPeriod,
                                $endOfPeriod,
                                $endOfDay,
                            ] = static::getPeriodLimits(
                                $baseDate,
                                $startTime,
                                $endTime
                            );

                            $startDayEvent
                                ->setUseTimezone(true)
                                ->setDtStart($startOfDay)
                                ->setDtEnd($startOfPeriod);
                            $endDayEvent
                                ->setUseTimezone(true)
                                ->setDtStart($endOfPeriod)
                                ->setDtEnd($endOfDay);

                            $calendar->addComponent($startDayEvent);
                            $calendar->addComponent($endDayEvent);
                            break;
                    }
                    break;
                case true:
                default:
                    $baseEvent->addExDate(new Carbon($date));

                    if ($exception->period !== "00:00-23:59") {
                        $startDayEvent = new Event();
                        $endDayEvent = new Event();

                        [$startTime, $endTime] = explode(
                            "-",
                            $exception->period
                        );
                        $startTime = explode(":", $startTime);
                        $endTime = explode(":", $endTime);

                        [
                            $startOfDay,
                            $startOfPeriod,
                            $endOfPeriod,
                            $endOfDay,
                        ] = static::getPeriodLimits(
                            new Carbon($date),
                            $startTime,
                            $endTime
                        );

                        $startDayEvent
                            ->setDtStart($startOfDay)
                            ->setDtEnd($startOfPeriod);
                        $endDayEvent
                            ->setDtStart($endOfPeriod)
                            ->setDtEnd($endOfDay);

                        $calendar->addComponent($startDayEvent);
                        $calendar->addComponent($endDayEvent);
                    }
                    break;
            }
        }
    }

    protected static function addWeekdaysExceptionScope(
        &$model,
        &$exception,
        &$calendar
    ) {
        foreach ($exception->scope as $day) {
            $startDayEvent = new Event();
            $endDayEvent = new Event();

            [$startTime, $endTime] = explode("-", $exception->period);
            $startTime = explode(":", $startTime);
            $endTime = explode(":", $endTime);

            $baseDate = $model::getFirstDateOnA($day, $model);
            [
                $startOfDay,
                $startOfPeriod,
                $endOfPeriod,
                $endOfDay,
            ] = $model::getPeriodLimits($baseDate, $startTime, $endTime);

            $recurrence = new RecurrenceRule();
            $recurrence
                ->setFreq(RecurrenceRule::FREQ_MONTHLY)
                ->setByDay(join(",", $exception->scope));

            $startDayEvent
                ->setUseTimezone(true)
                ->setDtStart($startOfDay)
                ->setDtEnd($startOfPeriod)
                ->setRecurrenceRule($recurrence);
            $endDayEvent
                ->setUseTimezone(true)
                ->setDtStart($endOfPeriod)
                ->setDtEnd($endOfDay)
                ->setRecurrenceRule($recurrence);

            $calendar->addComponent($startDayEvent);
            $calendar->addComponent($endDayEvent);
        }
    }

    protected static function addDates(&$model, &$exception, &$calendar)
    {
        foreach ($exception->scope as $date) {
            $baseDate = new Carbon(
                $date,
                new \DateTimeZone("America/Montreal")
            );

            if ($exception->period !== "00:00-23:59") {
                $startDayEvent = new Event();
                $endDayEvent = new Event();

                [$startTime, $endTime] = explode("-", $exception->period);
                $startTime = explode(":", $startTime);
                $endTime = explode(":", $endTime);

                [
                    $startOfDay,
                    $startOfPeriod,
                    $endOfPeriod,
                    $endOfDay,
                ] = $model::getPeriodLimits($baseDate, $startTime, $endTime);

                $startDayEvent
                    ->setUseTimezone(true)
                    ->setDtStart($startOfDay)
                    ->setDtEnd($startOfPeriod);
                $endDayEvent
                    ->setUseTimezone(true)
                    ->setDtStart($endOfPeriod)
                    ->setDtEnd($endOfDay);

                $calendar->addComponent($startDayEvent);
                $calendar->addComponent($endDayEvent);
            } else {
                $event = (new Event())
                    ->setDtStart($baseDate)
                    ->setDtEnd($baseDate)
                    ->setNoTime(true);
                $calendar->addComponent($event);
            }
        }
    }
}
