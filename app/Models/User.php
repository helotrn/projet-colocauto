<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Passport\HasApiTokens;

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

    public $collections = [];

    public function getAuthIdentifierName() {
        return 'id';
    }

    public function getAuthIdentifier() {
        return $this->{$this->getAuthIdentifierName()};
    }

    public function getAuthPassword() {
        return $this->password;
    }
}
