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
        return $this->hasMany(Loanable::class);
    }

    public function cars() {
        return $this->hasMany(Car::class);
    }

    public function tools() {
        return $this->hasMany(Tool::class);
    }
}
