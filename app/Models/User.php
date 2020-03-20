<?php

namespace App\Models;

use App\Models\Action;
use App\Models\Invoice;
use App\Models\Borrower;
use App\Models\File;
use App\Models\Loan;
use App\Models\Owner;
use App\Models\PaymentMethod;
use App\Transformers\UserTransformer;
use Auth;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends AuthenticatableBaseModel
{
    use HasApiTokens, Notifiable, SoftDeletes;

    public static $rules = [
        'address' => 'nullable',
        'date_of_birth' => 'nullable|date',
        'description' => 'nullable',
        'email' => 'email',
        'google_id' => 'nullable',
        'is_smart_phone' => 'nullable|boolean',
        'last_name' => 'nullable',
        'name' => 'nullable',
        'other_phone' => [
          'nullable',
          'regex:/^$|^[-1-9][-0-9]*$/',
        ],
        'password' => [
          'min:8',
        ],
        'phone' => [
          'nullable',
          'regex:/^$|^[-1-9][-0-9]*$/',
        ],
        'postal_code' => [
          'nullable',
          'regex:/^$|^[a-zA-Z][0-9][a-zA-Z]\s*[0-9][a-zA-Z][0-9]$/',
        ],
    ];

    public static $filterTypes = [
        'id' => 'number',
        'full_name' => 'text',
        'deleted_at' => 'date',
    ];

    public static function getRules($action = '', $auth = null) {
        if ($action === 'submit') {
            return array_merge(static::$rules, [
                'address' => 'required',
                'date_of_birth' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'postal_code' => 'required',
                'telephone' => 'required',
            ]);
        }

        return parent::getRules($action, $auth);
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

    private $stripeCustomerMemo;

    protected $fillable = [
        'name',
        'last_name',
        'google_id',
        'description',
        'date_of_birth',
        'address',
        'postal_code',
        'phone',
        'is_smart_phone',
        'other_phone',
    ];

    protected $hidden = ['password', 'current_bill'];

    protected $casts = [
        'balance' => 'decimal:2',
        'email_verified_at' => 'datetime',
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
        return $this->morphMany(Tag::class, 'taggable');
    }

    public function isAdmin() {
        return $this->role === 'admin';
    }

    public function isAdminOfCommunity($communityId) {
        return $this->communities()->where('communities.id', $communityId)
            ->whereHas('users', function ($q) {
                return $q->where('community_user.role', 'admin');
            })
            ->exists();
    }

    public function getLastInvoiceOrCreate() {
        if ($this->currentInvoice) {
            return $this->currentInvoice;
        }

        $invoice = new Invoice;
        $invoice->user_id = $this->id;
        $invoice->save();

        return $invoice;
    }

    public function getStripeCustomer() {
        if ($this->stripeCustomerMemo) {
            return $this->stripeCustomerMemo;
        }

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        $customers = \Stripe\Customer::all([
            'email' => $this->email,
            'limit' => 1,
        ]);

        $newCustomer = array_pop($customers->data);

        if (!$newCustomer) {
            $newCustomer = \Stripe\Customer::create([
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

        $this->stripeCustomerMemo = $newCustomer;

        return $this->stripeCustomerMemo;
    }

    public function addToBalance($amount) {
        $this->balance = floatval($this->balance) + $amount;
        $this->save();
    }

    public function removeFromBalance($amount) {
        var_dump($this->balance);
        $this->balance = floatval($this->balance) - $amount;
        var_dump($this->balance);
        if (floatval($this->balance) < 0) {
            return abort(400);
        }

        $this->save();
    }
}
