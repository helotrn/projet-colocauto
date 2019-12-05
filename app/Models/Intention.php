<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Intention extends BaseModel
{
    protected $fillable = [
        'name', 'object_type', 'variable', 'rule',
    ];
}
