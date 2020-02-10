<?php

namespace App\Models;

use App\Models\Loanable;
use App\Transformers\OwnerTransformer;

class Owner extends BaseModel
{
    protected $fillable = [];

    public static $transformer = OwnerTransformer::class;

    public $collections = ['loanables'];

    public $items = ['user'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function loanables() {
        return $this->hasMany(Loanable::class);
    }

    public function cars() {
        return $this->hasMany(Car::class);
    }

    public function bikes() {
        return $this->hasMany(Bike::class);
    }

    public function trailers() {
        return $this->hasMany(Trailer::class);
    }
}
