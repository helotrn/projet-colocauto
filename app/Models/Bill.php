<?php

namespace App\Models;

use App\Models\BillableItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Bill extends BaseModel
{
    protected $fillable = [
        'name', 'object_type', 'variable', 'rule',
    ];

    public $belongsTo = ['user'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    protected $with = ['billableItems'];

    public $collections = ['billableItems'];

    public function billableItems() {
        return $this->hasMany(BillableItem::class);
    }
}
