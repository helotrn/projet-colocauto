<?php

namespace App\Models;

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

    public $morphOnes = [
      'insurance' => 'fileable',
      'gaa' => 'fileable',
      'saaq' => 'fileable',
    ];

    public function user() {
        return $this->belongsTo(User::class);
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
