<?php

namespace App\Models;

use App\Calendar\AvailabilityHelper;
use App\Models\Community;
use App\Models\Loan;
use App\Models\Owner;
use App\Models\User;
use App\Transformers\LoanableTransformer;
use App\Casts\PointCast;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use MStaack\LaravelPostgis\Eloquent\PostgisTrait;

class Loanable extends BaseModel
{
    use PostgisTrait, SoftDeletes;

    public $readOnly = true;

    public static $transformer = LoanableTransformer::class;

    protected $appends = ["community_ids"];

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
    }

    public function getCommunityIdsAttribute()
    {
        $owner = $this->owner()->first();
        $loanableCommunities = [];
        if ($owner) {
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

    protected $postgisFields = ["position"];

    protected $postgisTypes = [
        "position" => [
            "geomtype" => "geography",
        ],
    ];

    protected $casts = [
        "position" => PointCast::class,
    ];

    protected $with = [];

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
        if (!is_a(\Carbon\Carbon::class, $departureAt)) {
            $departureAt = new \Carbon\Carbon($departureAt);
        }

        $returnAt = $departureAt->copy()->add($durationInMinutes, "minutes");

        $loanInterval = [$departureAt, $returnAt];

        // Ensure an exception is thrown if JSON is not properly decoded.
        $availabilityRules = $this->availability_json
            ? json_decode(
                $this->availability_json,
                true,
                512,
                JSON_THROW_ON_ERROR
            )
            : [];

        if (
            !AvailabilityHelper::isScheduleAvailable(
                [
                    "available" => "always" == $this->availability_mode,
                    "rules" => $availabilityRules,
                ],
                $loanInterval
            )
        ) {
            return false;
        }

        $query = Loan::where("loanable_id", $this->id);

        if ($ignoreLoanIds) {
            $query = $query->whereNotIn("loans.id", $ignoreLoanIds);
        }

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
            ->where("departure_at", "<", $returnAt)
            ->where("loanable_id", $this->id);

        return $query->get()->count() === 0;
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

        // Ensure an exception is thrown if JSON is not properly decoded.
        $availabilityRules = $this->availability_json
            ? json_decode(
                $this->availability_json,
                true,
                512,
                JSON_THROW_ON_ERROR
            )
            : [];

        $dailyIntervals = AvailabilityHelper::getScheduleDailyIntervals(
            ["rules" => $availabilityRules],
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
}
