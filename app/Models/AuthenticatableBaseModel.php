<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Molotov\Traits\BaseModel as BaseModelTrait;

class AuthenticatableBaseModel extends Authenticatable
{
    use BaseModelTrait;
}
