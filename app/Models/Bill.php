<?php

namespace App\Models;

use App\Models\BillableItem;
use App\Models\User;
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


    public $collections = ['billableItems'];

    public function billableItems() {
        return $this->hasMany(BillableItem::class);
    }
}
