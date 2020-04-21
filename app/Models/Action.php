<?php

namespace App\Models;

use App\Models\Loan;
use App\Models\User;
use App\Utils\TimestampWithTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vkovic\LaravelCustomCasts\HasCustomCasts;

class Action extends BaseModel
{
    use HasCustomCasts, SoftDeletes;

    protected $casts = [
        'executed_at' => TimestampWithTimezone::class,
    ];

    public $readOnly = true;

    public $items = ['loan', 'user'];

    public function loan() {
        return $this->belongsTo(Loan::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
