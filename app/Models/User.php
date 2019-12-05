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

class User extends BaseModel
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
    ];

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

    protected $with = ['paymentMethods', 'bills', 'files', 'actions'];

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
