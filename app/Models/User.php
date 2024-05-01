<?php

namespace App\Models;

use App\Events\UserEmailUpdated;
use App\Mail\PasswordRequest;
use App\Services\LocoMotionGeocoderService as LocoMotionGeocoder;
use App\Transformers\UserTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Mail;
use Noke;
use Stripe;

class User extends AuthenticatableBaseModel
{
    use HasApiTokens, Notifiable, SoftDeletes;

    public static $rules = [
        "accept_conditions" => ["accepted"],
        "gdpr" => ["accepted"],
        "newsletter" => "nullable|boolean",
        "address" => ["nullable"],
        "date_of_birth" => ["nullable", "date", "before:18 years ago"],
        "description" => "nullable",
        "email" => "email",
        "is_smart_phone" => "nullable|boolean",
        "last_name" => "nullable",
        "name" => ["nullable"],
        "other_phone" => ["nullable"],
        "password" => ["min:8"],
        "phone" => ["nullable"],
        "postal_code" => ["nullable"],
    ];

    public static $filterTypes = [
        "id" => "number",
        "created_at" => "date",
        "full_name" => "text",
        "email" => "text",
        "communities.name" => "text",
        "is_deleted" => "boolean",
    ];

    public $computed = ["admin_link", "color"];

    public static function getRules($action = "", $auth = null)
    {
        switch ($action) {
            case "submit":
                $rules = array_merge(static::$rules, [
                    "address" => "required",
                    "date_of_birth" => "required",
                    "first_name" => "required",
                    "last_name" => "required",
                    "telephone" => "required",
                ]);
                break;
            case "template":
                $rules = parent::getRules($action, $auth);
                $rules["name"][] = "required";
                break;
            default:
                $rules = parent::getRules($action, $auth);
                break;
        }

        if ($auth && ($auth->isAdmin() || $auth->isCommunityAdmin())) {
            unset($rules["accept_conditions"]);
            unset($rules["gdpr"]);
            unset($rules["newsletter"]);
            unset($rules["avatar"]);
        }

        return $rules;
    }

    public static $transformer = UserTransformer::class;

    public static function booted()
    {
        self::updated(function ($model) {
            // Detect email change
            if ($model->wasChanged("email")) {
                $previousEmail = $model->getOriginal("email");
                event(
                    new UserEmailUpdated($model, $previousEmail, $model->email)
                );
            }
        });
    }

    public static function getColumnsDefinition()
    {
        return [
            "*" => function ($query = null) {
                if (!$query) {
                    return "users.*";
                }

                return $query->selectRaw("users.*");
            },
            "full_name" => function ($query = null) {
                $sql = "CONCAT(users.name, ' ', users.last_name)";

                if (!$query) {
                    return \DB::raw($sql);
                }

                return $query->selectRaw("$sql AS full_name");
            },
        ];
    }

    public static $sizes = [
        "thumbnail" => "256x@fit",
    ];

    public static $sizesByField = [];

    protected $fillable = [
        "is_deactivated",
        "accept_conditions",
        "gdpr",
        "newsletter",
        "name",
        "last_name",
        "description",
        "date_of_birth",
        "address",
        "postal_code",
        "phone",
        "is_smart_phone",
        "other_phone",
    ];

    protected $hidden = ["password", "current_bill"];

    protected $casts = [
        "accept_conditions" => "boolean",
        "gdpr" => "boolean",
        "newsletter" => "boolean",
        "balance" => "decimal:2",
        "email_verified_at" => "datetime",
        "meta" => "array",
    ];

    protected $with = [];

    public $collections = [
        "actions",
        "invoices",
        "communities",
        "administrable_communities",
        "files",
        "loans",
        "loanables",
        "payment_methods",
        "expenses",
        "debited_refunds",
        "credited_refunds"
    ];

    public $items = ["borrower", "owner", "google_account"];

    public $morphOnes = [
        "avatar" => "imageable",
    ];

    public $morphManys = [
        "tags" => "taggable",
    ];

    public function avatar()
    {
        return $this->morphOne(Image::class, "imageable")->where(
            "field",
            "avatar"
        );
    }

    public function actions()
    {
        return $this->hasMany(Action::class);
    }

    public function googleAccount()
    {
        return $this->hasOne(GoogleAccount::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function borrower()
    {
        return $this->hasOne(Borrower::class);
    }

    // main_community
    //
    // In 99% of the cases, a user is (or should be) associated
    // with a single geographical community we call main_community.
    //
    // Exceptionnaly, other scenarios may involve non-geographical communities such as Eco-Village, Schools, etc.
    // But the app won't react well to these exceptions because of the assumption below.
    //
    public function getMainCommunityAttribute()
    {
        return $this->communities->first();
    }

    /**
     * Update User Address And Relocate Community
     *
     * @param  String $full_text_address
     * @return void
     *
     * SCENARIOS COVERED:
     *  1) Moved within the same community
     *  2) Moved from covered to non-covered
     *  3) Moved from non-covered to non-covered
     *  4) Moved from covered to covered
     *  5) Moved from non-covered to covered
     *
     */
    public function updateAddressAndRelocateCommunity(string $full_text_address)
    {
        // Geocode the text address into an Address object
        $address = LocoMotionGeocoder::geocode($full_text_address);
        $model = $this;

        // If the address has been located by the geocoder
        if ($address) {
            // Re-save newly formatted postal_code and address
            //
            // For the sake of keeping the data integrity of postal_code but technically we don't need it anymore
            // Save Quietly in order to prevent an infinite loop within this self:saved and $this->save();
            $localUser = User::withoutEvents(function () use (
                $address,
                $model
            ) {
                $localUser = User::find($model->id);
                $localUser->postal_code = $address->getPostalCode();
                $localUser->address = LocoMotionGeocoder::formatAddressToText(
                    $address
                );
                $localUser->save();
            });

            $coordinates = $address->getCoordinates();

            // Find if the new address is within a community
            $community = LocoMotionGeocoder::findCommunityFromCoordinates(
                $coordinates->getLatitude(),
                $coordinates->getLongitude()
            );

            // If so, attach or switch community
            if ($community) {
                $this->AttachMainCommunity($community);
            } elseif ($this->main_community) {
                // User has moved from covered to a non-covered area
                $this->DetachMainCommunity($this->main_community);
            }
        } else {
            // Users cannot have an invalid address
            abort(422, "The provided user address was not found.");
        }
    }

    /**
     * Attach a main community to the user
     *
     * @param  Community $community
     * @return void
     *
     * Two safety guards are in place:
     * 1) Since it's technically possible a use is attache twice to a community, we detach first.
     * 2) We don't switch community if the user is already in it
     */
    public function AttachMainCommunity(Community $community)
    {
        if ($this->main_community) {
            if ($this->main_community->id !== $community->id) {
                $this->communities()->detach($this->communities);
                $this->communities()->detach($community);
                $this->communities()->attach($community);
            }
        } else {
            $this->communities()->detach($community);
            $this->communities()->attach($community);
        }
    }

    /**
     * Detach a main community from the user
     *
     * @param  Community $community
     * @return void
     */
    public function DetachMainCommunity(Community $community)
    {
        $this->communities()->detach($community);
    }

    /**  communities() should be deprecated at some point as 99% of users have only one community
     *  use $this->community instead
     */
    public function communities()
    {
        return $this->belongsToMany(Community::class)
            ->using(Pivots\CommunityUser::class)
            ->withTimestamps()
            ->withPivot([
                "id",
                "approved_at",
                "created_at",
                "role",
                "suspended_at",
                "updated_at",
            ])
            ->distinct();
    }

    public function administrableCommunities()
    {
        return $this->belongsToMany(Community::class, 'community_admin')
            ->using(Pivots\CommunityAdmin::class)
            ->withTimestamps()
            ->withPivot([
                "id",
                "created_at",
                "organisation",
                "suspended_at",
                "updated_at",
            ])
            ->distinct();
    }

    public function approvedCommunities()
    {
        return $this->communities()
            ->whereNotNull("approved_at")
            ->whereNull("suspended_at");
    }

    public function defaultPaymentMethod()
    {
        return $this->hasOne(PaymentMethod::class)->whereIsDefault(true);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function loans()
    {
        return $this->hasManyThrough(Loan::class, Borrower::class);
    }

    public function loanables()
    {
        return $this->hasManyThrough(Loanable::class, Owner::class);
    }

    public function owner()
    {
        return $this->hasOne(Owner::class);
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function submit()
    {
        $this->submitted_at = new \DateTime();
        $this->save();
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, "taggable");
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function debitedRefunds()
    {
        return $this->hasMany(Refund::class, 'user_id');
    }

    public function creditedRefunds()
    {
        return $this->hasMany(Refund::class, 'credited_user_id');
    }

    public function isAdmin()
    {
        return $this->role === "admin";
    }

    public function isCommunityAdmin()
    {
        return $this->role === "community_admin";
    }

    public function isAdminOfCommunity(int $communityId)
    {
        // TODO: change this => not used ?
        return $this->communities()
            ->where("communities.id", $communityId)
            ->whereHas("users", function ($q) {
                return $q
                    ->where("community_user.role", "admin")
                    ->where("community_user.user_id", $this->id);
            })
            ->exists();
    }

    public function isAdminOfCommunityFor(int $userId)
    {
        // TODO: change this
        return $this->communities()
            ->whereHas("users", function ($q) {
                return $q
                    ->where("community_user.role", "admin")
                    ->where("community_user.user_id", $this->id);
            })
            ->whereHas("users", function ($q) use ($userId) {
                return $q->where("community_user.user_id", $userId);
            })
            ->exists();
    }

    public function belongsToTheSameCommunityAs(int $userId)
    {
        return $this->communities()
            ->whereHas("users", function ($q) {
                return $q
                    ->where("community_user.user_id", $this->id);
            })
            ->whereHas("users", function ($q) use ($userId) {
                return $q->where("community_user.user_id", $userId);
            })
            ->exists();
    }

    public function createInvoice($invoiceType)
    {
        $invoice = new Invoice();
        $invoice->user_id = $this->id;
        $invoice->period = \Carbon\Carbon::now()
            ->locale("fr_FR")
            ->format("m/Y");

        // Set the type of the invoice
        if ($invoiceType) {
            $invoice->type = $invoiceType;
        }

        $invoice->save();

        return $invoice;
    }

    public function getStripeCustomer()
    {
        return Stripe::getUserCustomer($this);
    }

    public function getAccessibleCommunityIds()
    {
        // TODO: add administrable communities here
        $communityIds = $this->communities
            ->whereNotNull("pivot.approved_at")
            ->whereNull("pivot.suspended_at")
            ->pluck("id");

        if ($communityIds->count() > 0) {
            $communityIds = $communityIds->concat(
                Community::parentOf($communityIds->toArray())->pluck("id")
            );
        }

        return $communityIds;
    }

    public function getSameCommunityUserIds()
    {
        $communityIds = $this->getAccessibleCommunityIds();
        $userIds = User::whereHas('communities', function ($q) use (
            $communityIds
        ) {
            return $q->whereIn('communities.id', $communityIds);
        })->pluck("id");

        return $userIds;
    }

    public function getNokeUser()
    {
        return Noke::findOrCreateUser($this);
    }

    public function addToBalance($amount)
    {
        $this->balance = floatval($this->balance) + $amount;
        $this->save();
    }

    public function removeFromBalance($amount)
    {
        $this->balance = floatval($this->balance) - $amount;

        if (floatval($this->balance) < 0) {
            return abort(400);
        }

        $this->save();
    }

    public function updateBalance($amount)
    {
        if ($amount > 0) {
            $this->addToBalance($amount);
        } elseif ($amount < 0) {
            $this->removeFromBalance(-$amount);
        }
    }

    public function scopeAccessibleBy(Builder $query, $user)
    {
        // TODO change this
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isCommunityAdmin()) {
            return $query
                // ...himself or herself
                ->whereId($user->id)
                // ...or belonging to a community of which he or she is an admin
                ->orWhere(function ($q) use ($user) {
                    return $q->whereHas("communities", function ($q) use ($user) {
                        $q->withAdminUser($user);
                    });
                });
        }

        // A user has access to...
        return $query
            // ...himself or herself
            ->whereId($user->id)
            // ...or belonging to a community of which he or she is a member
            ->orWhere(function ($q) use ($user) {
                return $q->whereHas("communities", function ($q) use ($user) {
                    $q->withApprovedUser($user);
                });
            });
    }

    public function scopeSearch(Builder $query, $q)
    {
        if (!$q) {
            return $query;
        }

        return $query->where(function ($q2) use ($q) {
            $sql = static::getColumnsDefinition()["full_name"]();
            return $q2->where(
                \DB::raw("unaccent($sql)"),
                "ILIKE",
                \DB::raw("unaccent('%$q%')")
            );
        });
    }

    public function scopeStalledAtRegistration(Builder $query)
    {
        return $query
            ->whereNull("submitted_at")
            ->whereDoesntHave("approvedCommunities")
            ->whereNull("suspended_at");
    }

    public function sendPasswordResetNotification($token)
    {
        Mail::to($this->email, $this->full_name)->queue(
            new PasswordRequest($this, $token)
        );
    }

    public function getFullNameAttribute()
    {
        return trim($this->name . " " . $this->last_name);
    }

    public function getAdminLinkAttribute()
    {
        return env("FRONTEND_URL") . "/admin/users/" . $this->id;
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

    /**
    * assign a color to each user, unique within its main community
    */
    public function getColorAttribute()
    {
        $me = $this->id;
        if( !$this->main_community || !$this->main_community->users ) return false;

        $index = $this->main_community->users->sortBy('id')->search(function ($user) use ($me) {
            return $user->id === $me;
        } );
        $nbOfColors = sizeof(config("app.colors"));
        return config("app.colors")[$index % $nbOfColors];
    }
}
