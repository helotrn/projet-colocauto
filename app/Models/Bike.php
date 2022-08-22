<?php

namespace App\Models;

use App\Models\Loanable;
use Illuminate\Database\Eloquent\Builder;

class Bike extends Loanable
{
    public static $rules = [
        "bike_type" => ["required"],
        "comments" => ["present"],
        "instructions" => ["present"],
        "location_description" => ["present"],
        "model" => ["required"],
        "name" => ["required"],
        "position" => ["required"],
        "size" => ["required"],
        "type" => ["required", "in:bike"],
    ];

    protected $fillable = [
        "is_self_service",
        "availability_json",
        "availability_mode",
        "bike_type",
        "comments",
        "instructions",
        "location_description",
        "model",
        "name",
        "position",
        "share_with_parent_communities",
        "size",
    ];

    public static function getColumnsDefinition()
    {
        return [
            "*" => function ($query = null) {
                if (!$query) {
                    return "bikes.*";
                }

                return $query->selectRaw("bikes.*");
            },
        ];
    }

    protected $table = "bikes";

    public $readOnly = false;

    protected $appends = ["type"];

    public function getTypeAttribute()
    {
        return "bike";
    }

    public function loans()
    {
        return $this->hasMany(Loan::class, "loanable_id");
    }
}
