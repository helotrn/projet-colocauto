<?php

namespace App\Models;

use Carbon\Carbon;

class Car extends Loanable
{
    public static $rules = [
        "brand" => ["required"],
        "comments" => ["present"],
        "engine" => ["required", "in:fuel,diesel,electric,hybrid"],
        "instructions" => ["present"],
        "insurer" => ["required"],
        "is_value_over_fifty_thousand" => ["boolean"],
        "location_description" => ["present"],
        "model" => ["required"],
        "name" => ["required"],
        "papers_location" => ["required", "in:in_the_car,to_request_with_car"],
        "pricing_category" => ["required", "in:small,large,electric"],
        "transmission_mode" => ["required", "in:manual,automatic"],
        "type" => ["required", "in:car"],
        "cost_per_km" => ["numeric", "present", "min:0"],
        "cost_per_month" => ["numeric", "present", "min:0"],
        "owner_compensation" => ["numeric", "present", "min:0"],
    ];

    public static function getRules($action = "", $auth = null)
    {
        $rules = parent::getRules($action, $auth);
        if($action === "template") {
            $rules["cost_per_km"][0] = "decimal";
            $rules["cost_per_month"][0] = "decimal";
            $rules["owner_compensation"][0] = "decimal";
        }
        return $rules;
    }

    public static function getColumnsDefinition()
    {
        return [
            "*" => function ($query = null) {
                if (!$query) {
                    return "cars.*";
                }

                return $query->selectRaw("cars.*");
            },
        ];
    }

    protected $table = "cars";

    public $readOnly = false;

    protected $fillable = [
        "is_self_service",
        "availability_json",
        "availability_mode",
        "brand",
        "comments",
        "engine",
        "has_informed_insurer",
        "instructions",
        "insurer",
        "is_value_over_fifty_thousand",
        "location_description",
        "model",
        "name",
        "papers_location",
        "plate_number",
        "pricing_category",
        "share_with_parent_communities",
        "transmission_mode",
        "year_of_circulation",
        "cost_per_km",
        "cost_per_month",
        "owner_compensation",
    ];

    public $items = ["owner", "community"];

    public $morphOnes = [
        "image" => "imageable",
        "report" => "fileable",
    ];

    protected $appends = ["type"];

    public function getTypeAttribute()
    {
        return "car";
    }

    public function image()
    {
        return $this->morphOne(Image::class, "imageable")->where(
            "field",
            "image"
        );
    }

    public function report()
    {
        return $this->morphOne(File::class, "fileable")->where(
            "field",
            "report"
        );
    }

    public function loans()
    {
        return $this->hasMany(Loan::class, "loanable_id");
    }

    public function coowners()
    {
        return $this->hasMany(Coowner::class, "loanable_id");
    }

    public function padlock()
    {
        return $this->hasOne(Padlock::class, "loanable_id")->where(
            \DB::raw("1 = 0")
        );
    }

    public function writeMonthlySharedExpenses()
    {
        // get the users of the same community that joined the community
        // before the current (previous ?) month
        $users = $this->owner->user->main_community->users()
            ->wherePivotNotBetween("approved_at", [
                Carbon::now()->startOfMonth(),
                Carbon::now()
            ])->get();

        $funds_tag = ExpenseTag::where('slug', 'funds');

        $period_start = Carbon::now()->startOfMonth()->subDay();
        $shared_cost = $this->cost_per_month / $users->count();
        foreach($users as $user) {
            Expense::create([
                "name" => "Provision ".$period_start->monthName." ".$period_start->year,
                "amount" => $shared_cost,
                "type" => "debit",
                "executed_at" => Carbon::now(),
                "user_id" => $user->id,
                "loanable_id" => $this->id,
                "expense_tag_id" => $funds_tag ? $funds_tag->first()->id : null,
            ]);
        }
    }
}
