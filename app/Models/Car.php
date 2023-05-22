<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Log;

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

    public function writeMonthlySharedExpenses($date = null, $dryrun = false)
    {
        if(!$date) {
            $date = CarbonImmutable::now();
        } elseif (is_string($date)) {
            $date = Carbon::createFromFormat('d-m-Y', $date);
        }
        Log::info("\n##################################\nCompute mounthy shared expenses for {$this->name}\n##################################\n");

        $community = ($this->owner && $this->owner->user) ? $this->owner->user->main_community : $this->community;
        // expenses on a loanable without community cannot be managed
        if(!$community) {
            Log::info("no community found");
            return;
        }

        $period_start = config("app.month_as_day") ? $date->startOfDay() : $date->startOfMonth();

        // get the users of the same community that joined the community
        // before the current (previous ?) month
        $users = $community->users()
            ->wherePivotNotBetween("approved_at", [
                $period_start,
                $date
            ])
            ->wherePivotNull("suspended_at")->get();

        Log::info("{$users->count()} people in the community");
        if( $users->count() === 0 ) return;

        $funds_tag = ExpenseTag::where('slug', 'funds');
        $compensation_tag = ExpenseTag::where('slug', 'compensation');

        $shared_cost = $this->cost_per_month / $users->count();
        $owner = $this->owner;
        if( $owner && $owner->user ) {
            $owner_compensation = $this->owner_compensation / $users->filter(function($u) use ($owner){ return $owner->user->id !== $u->id; })->count();
        } else {
            $owner_compensation = 0;
        }
        foreach($users as $user) {
            $period = "";
            if( config("app.month_as_day") ) {
                $period .= $period_start->day." ";
            }
            $period .= $period_start->locale('fr')->monthName." ".$period_start->year;
            $data = [
                "name" => "Provision ".$period,
                "amount" => number_format($shared_cost,2),
                "type" => "debit",
                "executed_at" => Carbon::now(),
                "user_id" => $user->id,
                "loanable_id" => $this->id,
                "expense_tag_id" => $funds_tag->count() ? $funds_tag->first()->id : null,
            ];
            Log::info("Attribute ${data["name"]} expense to $user->name $user->last_name (${data["amount"]}€)");
            Log::info(var_export(array_map(function($val){ return is_object($val) ? $val->toString() : $val; }, $data), true));
            if( !$dryrun ) {
                $expense = Expense::create($data);
                $expense->locked = true;
                $expense->save();
            }
            if( $owner_compensation && $this->owner->user->id !== $user->id ){
                $data2 = [
                    "name" => "Dédommagement propriétaire ".$period,
                    "amount" => number_format($owner_compensation,2),
                    "type" => "debit",
                    "executed_at" => Carbon::now(),
                    "user_id" => $user->id,
                    "loanable_id" => $this->id,
                    "expense_tag_id" => $compensation_tag->count() ? $compensation_tag->first()->id : null,
                ];
                Log::info("Attribute ${data2["name"]} owner expense to $user->name $user->last_name (${data2["amount"]}€)");
                Log::info(var_export(array_map(function($val){ return is_object($val) ? $val->toString() : $val; }, $data2), true));
                if( !$dryrun ) {
                    $expense = Expense::create($data2);
                    $expense->locked = true;
                    $expense->save();
                }
            }
        }
    }
}
