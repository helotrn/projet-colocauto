<?php

namespace App\Models;

use App\Casts\Uppercase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Padlock extends BaseModel
{
    public static $rules = [
        "external_id" => "required",
        "mac_address" => "required|unique:padlocks,mac_address",
        "name" => "required",
    ];

    public static $filterTypes = [
        "external_id" => "text",
        "name" => "text",
        "loanable.name" => "text",
        "loanable.id" => "number",
    ];

    protected $casts = [
        "mac_address" => Uppercase::class,
    ];

    protected $fillable = ["external_id", "mac_address", "name"];

    public $items = ["loanable"];

    public static function getColumnsDefinition()
    {
        return [
            "*" => function ($query = null) {
                if (!$query) {
                    return "padlocks.*";
                }

                return $query->selectRaw("padlocks.*");
            },

            "loanable_name" => function ($query = null) {
                if (!$query) {
                    return "loanables.name";
                }

                $query->selectRaw("loanables.name AS loanable_name");

                $query = static::addJoin(
                    $query,
                    "loanables",
                    "loanables.id",
                    "=",
                    "padlocks.loanable_id"
                );

                return $query;
            },
        ];
    }

    public function loanable()
    {
        return $this->belongsTo(Loanable::class);
    }

    public function scopeAccessibleBy(Builder $query, $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where("1 = 0");
    }

    public function scopeSearch(Builder $query, $q)
    {
        if (!$q) {
            return $query;
        }

        return $query->where(
            \DB::raw("unaccent(padlocks.name)"),
            "ILIKE",
            \DB::raw("unaccent('%$q%')")
        );
    }
}
