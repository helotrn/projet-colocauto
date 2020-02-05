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
        'name' => 'nullable',
        'email' => 'email',
        'last_name' => 'nullable',
        'google_id' => 'nullable',
        'description' => 'nullable',
        'date_of_birth' => 'nullable|date',
        'address' => 'nullable',
        'postal_code' => 'nullable',
        'phone' => 'nullable',
        'is_smart_phone' => 'nullable|boolean',
        'other_phone' => 'nullable',
        'approved_at' => 'nullable|date',
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

    public $collections = [
      'loans',
      'bills',
      'communities',
      'files',
      'paymentMethods',
    ];

    public $belongsTo = [
      'borrower',
    ];

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
            ->withTimestamps()
            ->withPivot(['role', 'created_at', 'updated_at']);
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
        return $this->communities->where('pivot.role', 'admin')->where('id', $communityId)->isNotEmpty();
    }
}
