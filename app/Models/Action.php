<?php

namespace App\Models;

use App\Models\Loan;
use App\Models\User;
use App\Casts\TimestampWithTimezoneCast;
use Illuminate\Database\Eloquent\SoftDeletes;

class Action extends BaseModel
{
    use SoftDeletes;

    protected $casts = [
        "executed_at" => TimestampWithTimezoneCast::class,
    ];

    public $readOnly = true;

    public $items = ["loan", "user"];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
