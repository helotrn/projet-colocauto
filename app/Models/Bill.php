<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends BaseModel
{
    use SoftDeletes;

    public static $rules = [
        'period' => 'required',
    ];

    protected $fillable = [
        'period',
    ];

    public $items = ['user'];

    public function user() {
        return $this->belongsTo(User::class);
    }


    public $collections = ['items'];

    public function items() {
        return $this->hasMany(BilledItem::class);
    }
}
