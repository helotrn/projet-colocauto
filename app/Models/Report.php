<?php

namespace App\Models;

use App\Models\Image;
use App\Models\Loanable;
use Illuminate\Database\Eloquent\Builder;

class Report extends BaseModel
{
    public static $rules = [
        "location" => ["required", "in:front,back,right,left,inside"],
        "scratch" => ["boolean"],
        "bumps" => ["boolean"],
        "stain" => ["boolean"],
        "details" => ["nullable", "string"],
    ];

    protected $fillable = ["location", "scratch", "bumps", "stain", "details", "loanable_id"];
    public $items = ["loanable"];

    public $collections = ["pictures"];
    public $morphManys = ["pictures" => "imageable"];
    public function pictures()
    {
        return $this->morphMany(Image::class, "imageable")->where(
            "field",
            "report_picture"
        );
    }

    public function loanable()
    {
        return $this->belongsTo(Loanable::class);
    }

    // for automatic image thumbnail creation
    public static $sizes = [
        "thumbnail" => "256x@fit",
    ];

    public function scopeAccessibleBy(Builder $query, $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }
        return $query->whereHas("loanable", function ($q) use ($user) {
            return $q->accessibleBy($user);
        });
    }
}
