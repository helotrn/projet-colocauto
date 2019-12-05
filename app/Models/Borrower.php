<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Borrower extends BaseModel
{
    protected $fillable = [
        'name', 'object_type', 'variable', 'rule',
    ];
}
