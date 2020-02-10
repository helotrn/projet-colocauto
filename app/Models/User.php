<?php

namespace App\Models;

use App\Models\Bill;
use App\Models\Borrower;
use App\Models\File;
use App\Models\Loan;
use App\Models\PaymentMethod;
use App\Transformers\UserTransformer;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends AuthenticatableBaseModel
{
    use HasApiTokens, Notifiable;

    public static $rules = [
        'address' => 'nullable',
        'approved_at' => 'nullable|date',
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
        'approved_at',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $with = [];

    public $collections = [
      'loans',
      'bills',
      'communities',
      'files',
      'paymentMethods',
    ];

    public $items = [
      'avatar',
      'borrower',
    ];

    public $morphOneField = [
        'avatar' => 'imageable',
    ];

    public function images() {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function avatar() {
        return $this->morphOne(Image::class, 'imageable')->where('field', 'avatar');
    }

    public function borrower() {
        return $this->belongsTo(Borrower::class);
    }

    public function loans() {
        return $this->hasManyThrough(Loan::class, Borrower::class);
    }

    public function bills() {
        return $this->hasMany(Bill::class);
    }

    public function communities() {
        return $this->belongsToMany(Community::class)
            ->using(Pivots\CommunityUser::class)
            ->withTimestamps()
            ->withPivot(['id', 'approved_at', 'created_at', 'role', 'suspended_at', 'updated_at']);
    }

    public function files() {
        return $this->hasMany(File::class);
    }

    public function paymentMethods() {
        return $this->hasMany(PaymentMethod::class);
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
}
