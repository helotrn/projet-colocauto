<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Molotov\Traits\BaseModel as BaseModelTrait;
use Molotov\Transformers\BaseTransformer;

class AuthenticatableBaseModel extends Authenticatable
{
    use BaseModelTrait;
}
