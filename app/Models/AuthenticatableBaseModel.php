<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AuthenticatableBaseModel extends Authenticatable
{
    use BaseModelTrait;
}
