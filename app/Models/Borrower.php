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
        'approved_at' => 'nullable|date',
        'submitted_at' => 'nullable|date',
    ];

    protected $fillable = [
        'drivers_license_number',
        'has_been_sued_last_ten_years',
        'noke_id',
        'approved_at',
        'submitted_at',
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

    public function isApproved() {
        return $this->approved_at !== null;
    }

    public function approve($user) {
        if ($user->isAdmin()) {
            $this->approved_at = now();
        }
    }
}
