<?php

namespace App\Models;

use App\Models\Community;
use App\Utils\ObjectTypeCast;
use Vkovic\LaravelCustomCasts\HasCustomCasts;

class Pricing extends BaseModel
{
    use HasCustomCasts;

    public static $rules = [
        'name' => 'required',
        'object_type' => 'required',
        'rule' => 'required',
    ];

    protected $casts = [
        'object_type' => ObjectTypeCast::class,
    ];

    protected $fillable = [
        'name',
        'object_type',
        'rule',
    ];

    public $items = ['community'];

    public function community() {
        return $this->belongsTo(Community::class);
    }
}
