<?php

namespace App\Models;

use App\Models\BillableItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Payment extends BaseModel
{
    protected $fillable = [
        'name', 'object_type', 'variable', 'rule',
    ];

    public $belongsTo = ['billableItem'];

    public function billableItem() {
        return $this->belongsTo(BillableItem::class);
    }
}
