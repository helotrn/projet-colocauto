<?php

namespace App\Models;

use App\Models\Object;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Owner extends BaseModel
{
    protected $fillable = [
        'name', 'object_type', 'variable', 'rule',
    ];

    protected $with = ['objects'];

    public $collections = ['objects'];

    public function objects() {
        return $this->hasMany(Object::class);
    }
}
