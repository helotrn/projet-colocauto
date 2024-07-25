<?php

namespace App\Models;

use App\Calendar\AvailabilityHelper;
use App\Exports\LoanableExport;
use App\Transformers\LoanableTransformer;
use App\Models\Car;
use App\Models\Loan;
use App\Models\Pricing;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Loanable extends BaseModel
{
    use SoftDeletes;

    public $readOnly = true;

    public static $export = LoanableExport::class;

    public static $transformer = LoanableTransformer::class;

    protected $appends = ["community_ids"];

    public static $filterTypes = [
        "id" => "number",
        "name" => "text",
        "type" => ["bike", "car", "trailer"],
        "community.name" => "text",
        "deleted_at" => "date",
        "is_deleted" => "boolean",
    ];

    public static $rules = [
        "comments" => ["present"],
        "instructions" => ["present"],
        "location_description" => ["present"],
        "name" => ["required"],
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

        self::created(function ($model) {
            // loanable is available by default
            $model->availability_mode = "always";
            $model->save();
        });

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
            // if the loanable is made always unavailable
            if( array_key_exists("availability_mode", $model->getChanges())
                && $model->availability_mode === "never"
                && $model->availability_json === "[]" ) {

                // cancel all not yet started loans
                foreach($model->loans->where("status", "=", "in_process") as $loan) {
                    $loan->cancel();
                    $loan->save();
                }
            }
        });
    }

    public function getCommunityIdsAttribute()
    {
        $owner = $this->owner()->first();
        $loanableCommunities = [];
        if ($owner && $owner->user) {
            if ($this->share_with_parent_communities) {
                $loanableCommunities = $owner->user
                    ->getAccessibleCommunityIds()
                    ->toArray();
            } else {
                $loanableCommunities = array_map(function ($c) {
                    return $c["id"];
                }, $owner->user->communities->toArray());
            }
        } elseif ($this->community) {
            if (
                $this->share_with_parent_communities &&
                $this->community["parent"]
            ) {
                $loanableCommunities = [
                    $this->community["id"],
                    $this->community["parent"]["id"],
                ];
            } else {
                $loanableCommunities = [$this->community["id"]];
            }
        }
        return array_filter($loanableCommunities);
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

                return static::addJoin(
                    $query,
                    "users as owner_users",
                    "owner_users.id",
                    "=",
                    "owners.user_id"
                );
            },

            "community_name" => function ($query = null) {
                if (!$query) {
                    return "loanable_communities.name";
                }

                $query->selectRaw("loanable_communities.name AS community_name");

                $query = static::addJoin(
                    $query,
                    "communities as loanable_communities",
                    "loanable_communities.id",
                    "=",
                    "loanables.community_id"
                );

                return $query;
            },
        ];
    }

    protected $casts = [];

    protected $with = [];

    public $computed = [
        "car_insurer",
        "events",
        "has_padlock",
        "estimated_cost",
        "balance",
        "stats",
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

    public $collections = ["loans", "coowners", "expenses"];

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function coowners()
    {
        return $this->hasMany(Coowner::class);
    }

    public function addCoowner(int $userId)
    {
        $coowner = new Coowner();
        $coowner->user_id = $userId;
        $coowner->loanable_id = $this->id;

        $this->coowners()->save($coowner);
    }

    public function removeCoowner(int $userId)
    {
        $this->coowners()
            ->where("user_id", "=", $userId)
            ->delete();
    }

    public function isCoowner(User $user): bool
    {
        return $this->coowners->where("user_id", $user->id)->isNotEmpty();
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function getAvailabilityRules()
    {
        try {
            return $this->availability_json
                ? json_decode(
                    $this->availability_json,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                )
                : [];
        } catch (\Throwable $th) {
            // Logging the error rather than throwing allows the rules to be modified.
            Log::error(
                "Could not parse availability json: \"$this->availability_json\" for loanable id: $this->id."
            );
            return [];
        }
    }

    public function isAvailable(
        $departureAt,
        $durationInMinutes,
        $ignoreLoanIds = []
    ) {
        if (!is_a(Carbon::class, $departureAt)) {
            $departureAt = new Carbon($departureAt);
        }

        $returnAt = $departureAt->copy()->add($durationInMinutes, "minutes");

        if (!$this->isLoanableScheduleOpen($departureAt, $returnAt)) {
            return false;
        }

        $query = Loan::where("loanable_id", $this->id);

        if ($ignoreLoanIds) {
            $query = $query->whereNotIn("loans.id", $ignoreLoanIds);
        }

        $query->isPeriodUnavailable($departureAt, $returnAt);

        return $query->get()->count() === 0;
    }

    public function isLoanableScheduleOpen($departureAt, $returnAt)
    {
        $loanInterval = [$departureAt, $returnAt];

        return AvailabilityHelper::isScheduleAvailable(
            [
                "available" => "always" == $this->availability_mode,
                "rules" => $this->getAvailabilityRules(),
            ],
            $loanInterval
        );
    }

    public function getCommunityForLoanBy(User $user): ?Community
    {
        $userComunities = $user->getAccessibleCommunityIds()->toArray();
        if ($this->owner) {
            $loanableCommunities = $this->owner->user
                ->getAccessibleCommunityIds()
                ->toArray();
        } else {
            $loanableCommunities = [
                $this->community->id,
                $this->community->parent_id,
            ];
        }

        $communityId = current(
            array_intersect($userComunities, $loanableCommunities)
        );
        return Community::where("id", $communityId)->first();
    }

    public function getEventsAttribute()
    {
        // Generate events for the next year.
        $dateRange = [new Carbon(), (new Carbon())->addYear()];

        $dailyIntervals = AvailabilityHelper::getScheduleDailyIntervals(
            ["rules" => $this->getAvailabilityRules()],
            $dateRange
        );

        // Create events from intervals.
        $events = [];
        foreach ($dailyIntervals as $interval) {
            $events[] = [
                "start" => $interval[0]->format("Y-m-d H:i:s"),
                "end" => $interval[1]->format("Y-m-d H:i:s"),
            ];
        }

        return $events;
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

    public function getEstimatedCostAttribute()
    {

        // get loanable community ...
        $community = $this->community;
        if( !$community ) {
            // ... or owner community
            $owner = $this->owner()->first();
            if( !$owner ) {
                return (object) [
                    "price" => 0,
                    "insurance" => 0,
                    "pricing" => "Gratuit",
                ];
            }
            $community = self::getCommunityForLoanBy($owner->user);
        }
        if($community) {
            $pricing = $community->getPricingFor($this);
        } else {
            $pricing = null;
        }
        if (!$pricing) {
            return (object) [
                "price" => 0,
                "insurance" => 0,
                "pricing" => "Gratuit",
            ];
        }

        // evaluate a fictive loan to get the price per km
        $departureAt = new Carbon();
        $car = Car::find($this->id)->toArray();
        $estimatedCost = $pricing->evaluateRule(
            1, // km
            0, // minutes
            $car,
            (object) [
                "days" => Loan::getCalendarDays($departureAt, $departureAt),
                "start" => Pricing::dateToDataObject($departureAt),
                "end" => Pricing::dateToDataObject($departureAt),
            ]
        );

        if (is_array($estimatedCost)) {
            [$price, $insurance] = $estimatedCost;
        } else {
            $price = $estimatedCost;
            $insurance = 0;
        }

        return (object) [
            "price" => $price,
            "insurance" => $insurance,
            "pricing" => $pricing->name,
            "cost_per_km" => $car['cost_per_km'],
            "cost_per_month" => $car['cost_per_month'],
        ];
    }

    public function handleInstructionVisibilityFor($user, $loan = null)
    {
        if (
            $user->isAdmin() ||
            ($this->owner && $this->owner->user->id === $user->id)
        ) {
            $this->makeVisible("instructions");
            return;
        }

        // Make instructions visible for an approved loan in process
        if (
            $loan &&
            $loan->loanable->id === $this->id &&
            $loan->status === "in_process" &&
            $loan->intention &&
            $loan->intention->isCompleted() &&
            $loan->borrower->user->id === $user->id
        ) {
            $this->makeVisible("instructions");
        } else {
            $this->makeHidden("instructions");
        }
    }

    public function getBalanceAttribute()
    {
        // get all expenses sorted by tag and type
        return Expense::where("loanable_id", $this->id)->get()
        ->groupBy(function($item){
            return $item->type.($item->tag ? '-'.$item->tag->slug : '');
        })->map(function($item){
            // compute the total for each group
            return [
                "total" => $item->sum('amount'),
                "name" => $item->first()->tag ? $item->first()->tag->name : 'Autres',
                "type" => $item->first()->type,
            ];
        });
    }

    public function getStatsAttribute()
    {
        $loans = Loan::where("loanable_id", $this->id)
            ->whereHas("handover", function ($query) {
                $query->whereNotNull('executed_at');
            })->get();

        return [
            "loans" => $loans->count(),
            "km" => $loans->map(function($loan){
                return $loan->handover->mileage_end - $loan->takeover->mileage_beginning;
            })->sum(),
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

        if ($user->isCommunityAdmin()) {
            // a community admin has access to ...
            return $query->where(function ($q) use ($user) {
                // loanables in communities where he or she is admin
                return $q->whereHas("community", function ($q) use ($user) {
                    $q->withAdminUser($user);
                });
            })
            ->orWhere(function ($q) use ($user) {
                // loanables where the owner ...
                return $q->whereHas("owner", function ($q) use ($user) {
                    return $q->whereHas("user", function ($q) use ($user) {
                        // ...belongs to a community where he or she is admin
                        return $q->whereHas("communities", function ($q) use ($user) {
                            $q->withAdminUser($user);
                        });
                    });
                });
            });
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
                // This check is necessary, since the Loanable class and this method is inherited,
                // but the 'type' field only exists in the loanable materialized view
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
                        return $q->whereIn("loanables.type", $allowedTypes);
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

    public function scopeHasAvailabilities(Builder $query)
    {
        return $query->where(function ($q) {
            return $q
                ->where("availability_mode", "!=", "never")
                ->orWhereJsonLength("availability_json", ">", 0);
        });
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
}
