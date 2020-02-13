<?php

namespace App\Models\Pivots;

use App\Models\BaseModelTrait;
use Illuminate\Database\Eloquent\Relations\Pivot;

abstract class BasePivot extends Pivot
{
    use BaseModelTrait;
}
