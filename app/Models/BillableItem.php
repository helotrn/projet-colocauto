<?php

namespace App\Models;

use App\Models\Bill;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BillableItem extends BaseModel
{
    protected $fillable = [
        'name', 'object_type', 'variable', 'rule',
    ];

    public $belongsTo = ['bill'];

    public function bill() {
        return $this->belongsTo(Bill::class);
    }
}
