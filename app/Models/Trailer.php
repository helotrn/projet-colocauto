<?php

namespace App\Models;

class Trailer extends Loanable
{
    public static function getColumnsDefinition()
    {
        return [
            "*" => function ($query = null) {
                if (!$query) {
                    return "trailers.*";
                }

                return $query->selectRaw("trailers.*");
            },
        ];
    }

    protected $fillable = [
        "is_self_service",
        "availability_json",
        "availability_mode",
        "comments",
        "instructions",
        "location_description",
        "maximum_charge",
        "dimensions",
        "name",
        "position",
        "share_with_parent_communities",
    ];

    public $readOnly = false;

    protected $appends = ["type"];

    public function getTypeAttribute()
    {
        return "trailer";
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
