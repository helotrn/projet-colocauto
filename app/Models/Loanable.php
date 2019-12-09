<?php

namespace App\Models;

use App\Models\Owner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Loanable extends BaseModel
{
    protected $table = ['loanables'];

    protected $fillable = [
    ];

    public $belongsTo = ['owner'];

    public function owner() {
        return $this->belongsTo(Owner::class);
    }
}
