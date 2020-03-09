<?php

namespace App\Models;

use App\Models\Bike;
use App\Models\Car;
use App\Models\Loanable;
use App\Models\Trailer;
use Illuminate\Database\Eloquent\SoftDeletes;

class Owner extends BaseModel
{
    use SoftDeletes;

    public static $rules = [
        'approved_at' => 'nullable|date',
        'submitted_at' => 'nullable|date',
    ];

    protected $fillable = [
        'approved_at',
        'submitted_at',
        'user_id',
    ];

    public $collections = [
      'loanables',
      'cars',
      'bikes',
      'trailers',
    ];

    public $items = [
      'user',
    ];

    public $morphOnes = [
        'licence' => 'imageable',
    ];

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
