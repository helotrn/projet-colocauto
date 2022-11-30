<?php

namespace App\Models;

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

    public function coowners()
    {
        return $this->hasMany(Coowner::class, "loanable_id");
    }
}
