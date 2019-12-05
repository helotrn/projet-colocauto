<?php

namespace App\Models;

use App\Models\Pricing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Community extends BaseModel
{
    public static $rules = [
        'name' => 'required',
    ];

    protected $fillable = [
        'name', 'description'
    ];

    protected $with = ['pricings'];

    public $collections = ['pricings'];

    public function pricings() {
        return $this->hasMany(Pricing::class);
    }
}
