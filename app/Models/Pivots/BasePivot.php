<?php

namespace App\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Molotov\Traits\BaseModel as BaseModelTrait;

abstract class BasePivot extends Pivot
{
    use BaseModelTrait;
}
