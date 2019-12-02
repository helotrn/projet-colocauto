<?php

namespace App\Models;

class Pricing extends BaseModel
{
    protected $fillable = [
        'name', 'object_type', 'variable', 'rule',
    ];

    public $belongsTo = ['community'];

    public function community() {
        return $this->belongsTo(Instance::class);
    }
}
