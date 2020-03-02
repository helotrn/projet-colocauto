<?php

namespace App\Models;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

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
        'user_id',
    ];

    public $items = ['user'];

    public $morphOnes = [
      'insurance' => 'fileable',
      'gaa' => 'fileable',
      'saaq' => 'fileable',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public $collections = ['loans'];

    public function loans() {
        return $this->hasMany(Loan::class);
    }

    public function getApprovedAttribute() {
        return !!$this->approved_at;
    }

    public function gaa() {
        return $this->morphOne(File::class, 'fileable')->where('field', 'gaa');
    }

    public function insurance() {
        return $this->morphOne(File::class, 'fileable')->where('field', 'insurance');
    }

    public function saaq() {
        return $this->morphOne(File::class, 'fileable')->where('field', 'saaq');
    }
}
