<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Car extends BaseModel
{
    protected $fillable = [
        'name', 'object_type', 'variable', 'rule',
    ];
}
