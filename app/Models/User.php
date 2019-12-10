<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Passport\HasApiTokens;
use App\Models\PaymentMethod;
use App\Models\Bill;
use App\Models\File;
use App\Models\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\UserTransformer;

class User extends AuthenticatableBaseModel
{
    use HasApiTokens, Notifiable;

    public static $rules = [
        'name' => 'required',
        'last_name' => 'required',
        'email' => 'required|email|unique:users,email',
        'password' => 'required',
        'google_id' => 'nullable',
        'description' => 'nullable',
        'date_of_birth' => 'date',
        'address' => 'required',
        'postal_code' => 'required',
        'phone' => 'required',
        'is_smart_phone' => 'boolean',
        'other_phone' => 'required',
        'approved_at' => 'nullable|date',
    ];

    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
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

    public static $transformer = UserTransformer::class;

    protected $hidden = ['password'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getAuthIdentifierName() {
        return 'id';
    }

    public function getAuthIdentifier() {
        return $this->{$this->getAuthIdentifierName()};
    }

    public function getAuthPassword() {
        return $this->password;
    }

    public $collections = ['paymentMethods', 'bills', 'files', 'actions'];

    public function paymentMethods() {
        return $this->hasMany(PaymentMethod::class);
    }

    public function bills() {
        return $this->hasMany(Bill::class);
    }

    public function files() {
        return $this->hasMany(File::class);
    }

    public function actions() {
        return $this->hasMany(Action::class);
    }
}
