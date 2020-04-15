<?php

namespace App\Models;

use App\Models\Community;
use App\Models\Loan;
use App\Models\Owner;
use App\Models\User;
use App\Transformers\LoanableTransformer;
use App\Utils\PointCast;
use Carbon\Carbon;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Eluceo\iCal\Component\Timezone;
use Eluceo\iCal\Component\TimezoneRule;
use Eluceo\iCal\Property\Event\RecurrenceRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Vkovic\LaravelCustomCasts\HasCustomCasts;

class Loanable extends BaseModel
{
    use HasCustomCasts, PostgisTrait, SoftDeletes;

    public $readOnly = 'true';

    public static $transformer = LoanableTransformer::class;

    public static $filterTypes = [
        'id' => 'number',
        'name' => 'text',
        'type' => ['bike', 'car', 'trailer'],
        'deleted_at' => 'date',
    ];

    public static $rules = [
        'name' => [ 'required' ],
        'position' => [ 'required' ],
        'type' => [
            'required',
            'in:car,bike,trailer',
        ],
        'location_description' => [ 'present' ],
        'instructions' => [ 'required' ],
        'comments' => [ 'required' ],
    ];

    public static $sizes = [
        'thumbnail' => '256x@fit',
    ];

    public static function getRules($action = '', $auth = null) {
        if ($action === 'update') {
            return array_diff_key(static::$rules, [ 'type' => false ]);
        }

        return parent::getRules($action, $auth);
    }

    public static function boot() {
        parent::boot();

        self::saved(function ($model) {
            $calendar = new Calendar("locomotion.app/api/loanables/{$model->id}.ics");

            $baseEvent = new Event();

            $tz = $model::buildTimezone();
            $dtz = new \DateTimeZone('America/Montreal');
            $calendar->setTimezone($tz);

            $byDays = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];

            switch ($model->availability_mode) {
                case 'always':
                    $exceptions = json_decode($model->availability_json) ?: [];

                    foreach ($exceptions as $exception) {
                        switch ($exception->type) {
                            case 'dates':
                                static::addDates($model, $exception, $calendar);
                                break;
                            default:
                                break;
                        }
                    }
                    break;
                case 'never':
                default:
                    $baseEvent
                        ->setDtStart(new \DateTime($model->created_at->format('Y-m-d')))
                        ->setDtEnd(new \DateTime($model->created_at->format('Y-m-d')))
                        ->setNoTime(true);

                    $recurrence = new RecurrenceRule();
                    $recurrence->setFreq(RecurrenceRule::FREQ_MONTHLY);

                    $exceptions = json_decode($model->availability_json) ?: [];

                    foreach ($exceptions as $exception) {
                        switch ($exception->type) {
                            case 'dates':
                                static::addDatesException($baseEvent, $exception, $calendar);
                                break;
                            case 'weekdays':
                                $byDays = array_diff($byDays, $exception->scope);

                                if ($exception->period !== '00:00-23:59') {
                                    static::addWeekdaysExceptionScope($model, $exception, $calendar);
                                }
                                break;
                        }
                    }

                    if (!empty($byDays)) {
                        $recurrence->setByDay(join(',', $byDays));
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

    protected $hidden = ['availability_ics'];

    protected $postgisFields = [
        'position',
    ];

    protected $postgisTypes = [
        'position' => [
            'geomtype' => 'geography',
        ],
    ];

    protected $casts = [
        'position' => PointCast::class,
    ];

    protected $with = ['image'];

    public $computed = ['events', 'has_padlock', 'position_google'];

    public $items = ['owner', 'community', 'image', 'padlock'];

    public function community() {
        return $this->belongsTo(Community::class);
    }

    public function image() {
        return $this->belongsTo(Image::class);
    }

    public function owner() {
        return $this->belongsTo(Owner::class);
    }

    public function padlock() {
        return $this->hasOne(Padlock::class);
    }

    public $collections = ['loans'];

    public function loans() {
        return $this->hasMany(Loan::class);
    }

    public function isAvailable($departureAt, $durationInMinutes) {
        $ical = new \ICal\ICal($this->availability_ics, [
            'defaultTimeZone' => 'America/Toronto',
            'defaultWeekStart' => 'SU',
            'filterDaysBefore' => 1,
            'filterDaysAfter' => 365,
        ]);

        $events = $ical->eventsFromRange(
            $departureAt,
            $departureAt->add($durationInMinutes, 'minute')
        );

        return empty($events);
    }

    public function getEventsAttribute() {
        try {
            $ical = new \ICal\ICal($this->availability_ics, [
                'defaultTimeZone' => 'America/Toronto',
                'defaultWeekStart' => 'SU',
                'filterDaysBefore' => 1,
                'filterDaysAfter' => 365,
            ]);

            $events = [];
            foreach ($ical->events() as $event) {
                $startDate = new Carbon($event->dtstart);
                $endDate = new Carbon($event->dtend);
                $period = $startDate->format('H:i') . '-' . $endDate->format('H:i');

                $fullDay = $period === '00:00-00:00';

                $events[] = $fullDay
                    ? [
                        'start' => $startDate->format('Y-m-d'),
                        'end' => $endDate->format('Y-m-d'),
                        'period' => $startDate->format('H:i') . '-' . $endDate->format('H:i'),
                    ]
                    : [
                        'start' => $startDate->format('Y-m-d H:i'),
                        'end' => $endDate->format('Y-m-d H:i'),
                        'period' => $startDate->format('H:i') . '-' . $endDate->format('H:i'),
                    ];
            }
            return $events;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getHasPadlockAttribute() {
        return !!$this->padlock;
    }

    public function getPositionGoogleAttribute() {
        return [
            'lat' => $this->position[0],
            'lng' => $this->position[1],
        ];
    }

    public function scopeAccessibleBy(Builder $query, $user) {
        if ($user->isAdmin()) {
            return $query;
        }

        $allowedTypes = ['bike', 'trailer'];
        if ($user->borrower && $user->borrower->validated) {
            $allowedTypes[] = 'car';
        }

        $query = $query
            // A user has access to...
            ->where(function ($q) use ($user, $allowedTypes) {
                $communityIds = $user->communities->pluck('id');

                return $q->where(function ($q) use ($communityIds) {
                    return $q
                        // ...loanables belonging to its accessible communities...
                        ->whereHas('community', function ($q) use ($communityIds) {
                            return $q->whereIn(
                                'communities.id',
                                $communityIds
                            );
                        })
                        // ...or belonging to owners of his accessible communities
                        // (communities through user through owner)
                        ->orWhereHas('owner', function ($q) use ($communityIds) {
                            return $q->whereHas('user', function ($q) use ($communityIds) {
                                return $q->whereHas('communities', function ($q) use ($communityIds) {
                                    return $q->whereIn('community_user.community_id', $communityIds);
                                });
                            });
                        });
                // ...and cars are only allowed if the borrower profile is approved
                })->whereIn('type', $allowedTypes);
            });

        if ($user->owner) {
            // ...and his/her own cars even if the borrower profile is not approved
            $query = $query->orWhereHas('owner', function ($q) use ($user) {
                return $q->where('owners.id', $user->owner->id);
            });
        }

        return $query;
    }

    public function scopeSearch(Builder $query, $q) {
        if (!$q) {
            return $query;
        }

        return $query->where(
            \DB::raw('unaccent(name)'),
            'ILIKE',
            \DB::raw("unaccent('%$q%')")
        );
    }

    protected static function buildTimezone() {
        $tzName  = 'America/Montreal';
        $dtz = new \DateTimeZone($tzName);
        date_default_timezone_set($tzName);

        $tzRuleDst = new TimezoneRule(TimezoneRule::TYPE_DAYLIGHT);
        $tzRuleDst->setTzName('EDT');
        $tzRuleDst->setDtStart(new \DateTime('2007-11-04 02:00:00', $dtz));
        $tzRuleDst->setTzOffsetFrom('-0400');
        $tzRuleDst->setTzOffsetTo('-0500');

        $dstRecurrenceRule = new RecurrenceRule();
        $dstRecurrenceRule->setFreq(RecurrenceRule::FREQ_YEARLY);
        $dstRecurrenceRule->setByMonth(3);
        $dstRecurrenceRule->setByDay('2SU');
        $tzRuleDst->setRecurrenceRule($dstRecurrenceRule);

        $tzRuleStd = new TimezoneRule(TimezoneRule::TYPE_STANDARD);
        $tzRuleStd->setTzName('EST');
        $tzRuleStd->setDtStart(new \DateTime('2007-03-11 02:00:00', $dtz));
        $tzRuleStd->setTzOffsetFrom('-0500');
        $tzRuleStd->setTzOffsetTo('-0400');

        $stdRecurrenceRule = new RecurrenceRule();
        $stdRecurrenceRule->setFreq(RecurrenceRule::FREQ_YEARLY);
        $stdRecurrenceRule->setByMonth(10);
        $stdRecurrenceRule->setByDay('2SU');
        $tzRuleStd->setRecurrenceRule($stdRecurrenceRule);

        $tz = new Timezone($tzName);
        $tz->addComponent($tzRuleDst);
        $tz->addComponent($tzRuleStd);

        return $tz;
    }

    protected static function getFirstDateOnA($day, $model) {
        $date = new Carbon($model->created_at);
        $days = [
            'SU' => 'sunday',
            'MO' => 'monday',
            'TU' => 'tuesday',
            'WE' => 'wednesday',
            'TH' => 'thursday',
            'FR' => 'friday',
            'SA' => 'saturday',
        ];
        $strDay = $days[$day];
        return $date->modify("next $strDay");
    }

    protected static function getPeriodLimits($baseDate, $startTime, $endTime) {
        return [
            $baseDate->copy()->setTime(0, 0, 0),
            $baseDate->copy()->setTime(
                $startTime[0],
                $startTime[1],
                0
            ),
            $baseDate->copy()->setTime(
                $endTime[0],
                $endTime[1],
                0
            ),
            $baseDate->copy()->setTime(23, 59, 59),
        ];
    }

    protected static function addDatesException(&$baseEvent, &$exception, &$calendar) {
        foreach ($exception->scope as $date) {
            switch ($exception->available) {
                case false:
                    $baseDate = new Carbon($date, new \DateTimeZone('America/Montreal'));
                    switch ($exception->period) {
                        case '00:00-23:59':
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

                            [$startTime, $endTime] = explode('-', $exception->period);
                            $startTime = explode(':', $startTime);
                            $endTime = explode(':', $endTime);

                            [$startOfDay, $startOfPeriod, $endOfPeriod, $endOfDay]
                                = static::getPeriodLimits($baseDate, $startTime, $endTime);

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

                    if ($exception->period !== '00:00-23:59') {
                        $startDayEvent = new Event();
                        $endDayEvent = new Event();

                        [$startTime, $endTime] = explode('-', $exception->period);
                        $startTime = explode(':', $startTime);
                        $endTime = explode(':', $endTime);

                        [$startOfDay, $startOfPeriod, $endOfPeriod, $endOfDay]
                            = static::getPeriodLimits(new Carbon($date), $startTime, $endTime);

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

    protected static function addWeekdaysExceptionScope(&$model, &$exception, &$calendar) {
        foreach ($exception->scope as $day) {
            $startDayEvent = new Event();
            $endDayEvent = new Event();

            [$startTime, $endTime] = explode('-', $exception->period);
            $startTime = explode(':', $startTime);
            $endTime = explode(':', $endTime);

            $baseDate = $model::getFirstDateOnA($day, $model);
            [$startOfDay, $startOfPeriod, $endOfPeriod, $endOfDay]
                = $model::getPeriodLimits($baseDate, $startTime, $endTime);

            $recurrence = new RecurrenceRule();
            $recurrence
                ->setFreq(RecurrenceRule::FREQ_MONTHLY)
                ->setByDay(join(',', $exception->scope));

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

    protected static function addDates(&$model, &$exception, &$calendar) {
        foreach ($exception->scope as $date) {
            $baseDate = new Carbon($date, new \DateTimeZone('America/Montreal'));

            if ($exception->period !== '00:00-23:59') {
                $startDayEvent = new Event();
                $endDayEvent = new Event();

                [$startTime, $endTime] = explode('-', $exception->period);
                $startTime = explode(':', $startTime);
                $endTime = explode(':', $endTime);

                [$startOfDay, $startOfPeriod, $endOfPeriod, $endOfDay]
                    = $model::getPeriodLimits($baseDate, $startTime, $endTime);

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
