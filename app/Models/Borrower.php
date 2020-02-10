<?php

namespace App\Models;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use App\Transformers\BorrowerTransformer;

class Borrower extends BaseModel
{
    public static $rules = [
        'drivers_license_number' => 'nullable',
        'has_been_sued_last_ten_years' => 'boolean',
        'noke_id' => 'nullable',
    ];

    protected $fillable = [
        'drivers_license_number',
        'has_been_sued_last_ten_years',
        'noke_id',
    ];

    public static $transformer = BorrowerTransformer::class;

    public $belongsTo = ['user'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public $collections = ['loans'];

    public function loans() {
        return $this->hasMany(Loan::class);
    }
}
