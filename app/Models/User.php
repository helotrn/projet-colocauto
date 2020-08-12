<?php

namespace App\Models;

use App\Mail\PasswordRequest;
use App\Models\Action;
use App\Models\Borrower;
use App\Models\File;
use App\Models\Invoice;
use App\Models\Loan;
use App\Models\Owner;
use App\Models\PaymentMethod;
use App\Services\NokeService;
use App\Transformers\UserTransformer;
use Auth;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Mail;
use Laravel\Passport\HasApiTokens;

class User extends AuthenticatableBaseModel
{
    use HasApiTokens, Notifiable, SoftDeletes;

    public static $rules = [
        'accept_conditions' => ['accepted'],
        'address' => ['nullable'],
        'date_of_birth' => [
            'nullable',
            'date',
            'before:18 years ago',
        ],
        'description' => 'nullable',
        'email' => 'email',
        'is_smart_phone' => 'nullable|boolean',
        'last_name' => 'nullable',
        'name' => ['nullable'],
        'other_phone' => [
          'nullable',
        ],
        'password' => [
          'min:8',
        ],
        'phone' => ['nullable'],
        'postal_code' => [
          'nullable',
          'regex:/^$|^[a-zA-Z][0-9][a-zA-Z]\s*[0-9][a-zA-Z][0-9]$/',
        ],
    ];

    public static $filterTypes = [
        'id' => 'number',
        'created_at' => 'date',
        'full_name' => 'text',
        'deleted_at' => 'date',
        'communities.id' => [
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
    ];

    public static function getRules($action = '', $auth = null) {
        switch ($action) {
            case 'submit':
                $rules = array_merge(static::$rules, [
                    'address' => 'required',
                    'date_of_birth' => 'required',
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'postal_code' => 'required',
                    'telephone' => 'required',
                ]);
                break;
            case 'template':
                $rules = parent::getRules($action, $auth);
                $rules['name'][] = 'required';
                $rules['phone'][] = 'required';
                $rules['address'][] = 'required';
                $rules['postal_code'][] = 'required';
                break;
            default:
                $rules = parent::getRules($action, $auth);
                break;
        }

        if ($auth && $auth->isAdmin()) {
            unset($rules['accept_conditions']);
            unset($rules['avatar']);
        }

        return $rules;
    }

    public static $transformer = UserTransformer::class;

    public static function getColumnsDefinition() {
        return [
            '*' => function ($query = null) {
                return $query->selectRaw('users.*');
            },
            'full_name' => function ($query = null) {
                $sql = "CONCAT(users.name, ' ', users.last_name)";

                if (!$query) {
                    return \DB::raw($sql);
                }

                return $query->selectRaw("$sql AS full_name");
            }
        ];
    }

    public static $sizes = [
        'thumbnail' => '256x@fit',
    ];

    public static $sizesByField = [];

    protected $fillable = [
        'accept_conditions',
        'name',
        'last_name',
        'description',
        'date_of_birth',
        'address',
        'postal_code',
        'phone',
        'is_smart_phone',
        'opt_in_newsletter',
        'other_phone',
    ];

    protected $hidden = ['password', 'current_bill'];

    protected $casts = [
        'accept_conditions' => 'boolean',
        'balance' => 'decimal:2',
        'email_verified_at' => 'datetime',
        'opt_in_newsletter' => 'boolean',
    ];

    protected $with = [];

    public $collections = [
      'actions',
      'invoices',
      'communities',
      'files',
      'loans',
      'loanables',
      'payment_methods',
    ];

    public $items = [
      'borrower',
      'owner',
      'google_account',
    ];

    public $morphOnes = [
        'avatar' => 'imageable',
    ];

    public $morphManys = [
        'tags' => 'taggable',
    ];

    public function avatar() {
        return $this->morphOne(Image::class, 'imageable')->where('field', 'avatar');
    }

    public function actions() {
        return $this->hasMany(Action::class);
    }

    public function googleAccount() {
        return $this->hasOne(GoogleAccount::class);
    }

    public function invoices() {
        return $this->hasMany(Invoice::class);
    }

    public function currentInvoice() {
        return $this->hasOne(Invoice::class)
            ->orderBy('created_at', 'desc')
            ->whereNull('paid_at');
    }

    public function borrower() {
        return $this->hasOne(Borrower::class);
    }

    public function communities() {
        $relation = $this->belongsToMany(Community::class)
            ->using(Pivots\CommunityUser::class)
            ->withTimestamps()
            ->withPivot(['id', 'approved_at', 'created_at', 'role', 'suspended_at', 'updated_at']);

        $user = Auth::user();
        if ($user && $user->isAdmin()) {
            return $relation;
        }

        return $relation->whereSuspendedAt(null);
    }

    public function defaultPaymentMethod() {
        return $this->hasOne(PaymentMethod::class)->whereIsDefault(true);
    }

    public function files() {
        return $this->hasMany(File::class);
    }

    public function loans() {
        return $this->hasManyThrough(Loan::class, Borrower::class);
    }

    public function loanables() {
        return $this->hasManyThrough(Loanable::class, Owner::class);
    }

    public function owner() {
        return $this->hasOne(Owner::class);
    }

    public function paymentMethods() {
        return $this->hasMany(PaymentMethod::class);
    }

    public function submit() {
        $this->submitted_at = new \DateTime;
        $this->save();
    }

    public function tags() {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function isAdmin() {
        return $this->role === 'admin';
    }

    public function isAdminOfCommunity(int $communityId) {
        return $this->communities()->where('communities.id', $communityId)
            ->whereHas('users', function ($q) {
                return $q
                    ->where('community_user.role', 'admin')
                    ->where('community_user.user_id', $this->id);
            })
            ->exists();
    }

    public function isAdminOfCommunityFor(int $userId) {
        return $this->communities()
            ->whereHas('users', function ($q) {
                return $q
                    ->where('community_user.role', 'admin')
                    ->where('community_user.user_id', $this->id);
            })
            ->whereHas('users', function ($q) use ($userId) {
                return $q->where('community_user.user_id', $userId);
            })
            ->exists();
    }

    public function getLastInvoiceOrCreate() {
        if ($this->currentInvoice) {
            return $this->currentInvoice;
        }

        $invoice = new Invoice;
        $invoice->user_id = $this->id;
        $invoice->period = \Carbon\Carbon::now()->locale('fr_FR')->format('m/Y');
        $invoice->save();

        return $invoice;
    }

    public function getStripeCustomer() {
        if (app()->environment() === 'testing') {
            return; // TODO mock
        }

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        $customers = \Stripe\Customer::all([
            'email' => $this->email,
            'limit' => 1,
        ]);

        $customer = array_pop($customers->data);

        if (!$customer) {
            $customer = \Stripe\Customer::create([
                'description' => "{$this->name} {$this->last_name} "
                    . "<{$this->email}> ({$this->id})",
                'email' => $this->email,
                'name' => "{$this->name} {$this->last_name}",
                'address' => [
                    'line1' => $this->address,
                    'country' => 'CA',
                    'postal_code' => $this->postal_code,
                ],
            ]);
        }

        return $customer;
    }

    public function getNokeUser() {
        $nokeService = new NokeService(new HttpClient);

        $nokeUser = $nokeService->findOrCreateUser($this);

        return $nokeUser;
    }

    public function addToBalance($amount) {
        $this->balance = floatval($this->balance) + $amount;
        $this->save();
    }

    public function removeFromBalance($amount) {
        $this->balance = floatval($this->balance) - $amount;

        if (floatval($this->balance) < 0) {
            return abort(400);
        }

        $this->save();
    }

    public function updateBalance($amount) {
        if ($amount > 0) {
            $this->updateBalance($amount);
        } elseif ($amount < 0) {
            $this->removeFromBalance(-$amount);
        }
    }

    public function scopeAccessibleBy(Builder $query, $user) {
        if ($user->isAdmin()) {
            return $query;
        }

        // A user has access to...
        return $query
            // ...himself or herself
            ->whereId($user->id)
            // ...or belonging to a community of which he or she is a member
            ->orWhere(function ($q) use ($user) {
                return $q->whereHas('communities', function ($q2) use ($user) {
                    return $q2->whereHas('users', function ($q3) use ($user) {
                        return $q3->where('community_user.user_id', $user->id);
                    });
                });
            });
    }

    public function scopeSearch(Builder $query, $q) {
        if (!$q) {
            return $query;
        }

        return $query->where(function ($q2) use ($q) {
            return $q2
                ->where(
                    \DB::raw('unaccent(name)'),
                    'ILIKE',
                    \DB::raw("unaccent('%$q%')")
                )
                ->orWhere(
                    \DB::raw('unaccent(last_name)'),
                    'ILIKE',
                    \DB::raw("unaccent('%$q%')")
                );
        });
    }

    public function sendPasswordResetNotification($token) {
        Mail::to($this->email, $this->name . ' ' . $this->last_name)
          ->queue(new PasswordRequest($this, $token));
    }
}
