<?php

namespace App\Models;

use App\Models\Pricing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\CommunityTransformer;

class Community extends BaseModel
{
    public static $rules = [
        'name' => 'required',
        'description' => 'nullable',
        'area' => 'nullable',
    ];

    protected $fillable = [
        'name',
        'description',
        'territory',
    ];

    public static $transformer = CommunityTransformer::class;

    public $collections = ['users', 'pricings'];

    public function users() {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivot(['role', 'created_at', 'updated_at']);
    }

    public function pricings() {
        return $this->hasMany(Pricing::class);
    }
}
