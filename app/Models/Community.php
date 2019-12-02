<?php

namespace App\Models;

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
