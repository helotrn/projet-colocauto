<?php

namespace App\Models;

use App\Models\Loan;
use App\Models\User;
use App\Utils\TimestampWithTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vkovic\LaravelCustomCasts\HasCustomCasts;

class Borrower extends BaseModel
{
    use HasCustomCasts, SoftDeletes;

    public static $rules = [
        'drivers_license_number' => [ 'nullable' ],
        'has_been_sued_last_ten_years' => [ 'boolean' ],
        'noke_id' => [ 'nullable' ],
        'approved_at' => [
            'nullable',
            'date',
        ],
        'submitted_at' => [
            'nullable',
            'date',
        ],
    ];

    protected $fillable = [
        'drivers_license_number',
        'has_been_sued_last_ten_years',
        'noke_id',
        'submitted_at',
        'user_id',
    ];

    protected $casts = [
        'approved_at' => TimestampWithTimezone::class,
        'suspended_at' => TimestampWithTimezone::class,
    ];

    public $items = ['user'];

    public $computed = ['approved', 'is_complete', 'suspended', 'validated'];

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

    public function getIsCompleteAttribute() {
        return !!$this->drivers_license_number && !$this->has_been_sued_last_ten_years
            && !!$this->gaa && !!$this->saaq && !!$this->insurance;
    }

    public function getApprovedAttribute() {
        return !!$this->approved_at;
    }

    public function getSuspendedAttribute() {
        return !!$this->suspended_at;
    }

    public function getValidatedAttribute() {
        return $this->approved && !$this->suspended;
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
