<?php

namespace App\Models;

use App\Transformers\BaseTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AuthenticatableBaseModel extends Authenticatable
{
    use BaseModelTrait;
}
