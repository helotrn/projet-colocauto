<?php

namespace App\Models;

use App\Models\Loanable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\OwnerTransformer;

class Owner extends BaseModel
{
    public static $rules = [
        'submitted_at' => 'required|date',
        'approved_at' => 'required|date',
    ];
    
    protected $fillable = [
        'submitted_at',
        'approved_at',
    ];

    public static $transformer = OwnerTransformer::class;

    public $collections = ['loanables'];

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
