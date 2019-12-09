<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
}
