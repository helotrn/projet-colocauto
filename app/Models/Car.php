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
        Log::info("\n##################################\nCompute mounthy shared expenses for {$this->name} on {$date->toDateString()}\n##################################\n");

        $community = ($this->owner && $this->owner->user) ? $this->owner->user->main_community : $this->community;
        // expenses on a loanable without community cannot be managed
        if(!$community) {
            Log::info("no community found");
            return;
        }

        if( config("app.month_as_day") ) {
            $period_start = $date->copy()->startOfDay();
        } else if( $this->created_at > $date->copy()->startOfMonth() ) {
            $period_start = $this->created_at;
        } else {
            $period_start = $date->copy()->startOfMonth();
        }
        Log::info("Period start: ".$period_start->toDateString());

        // get the users of the same community
        $users = $community->users()
            ->wherePivotNull("suspended_at")->get()
            ->map(function($user) use ($period_start, $date) {
                // compute the number of days the car was available to the user
                $start = $period_start->maximum($user->borrower->approved_at)->startOfDay();
                $user->days_present = $start->diffInDays($date) + 1;
                Log::info("$user->name $user->last_name : ".$start->toDateString().", $user->days_present presence");
                return $user;
            });

        $total_days_present = $users->reduce(function($sum, $user) {
            return $sum + $user->days_present;
        }, 0);
        Log::info("Total presence days: $total_days_present");

        $owner = $this->owner;
        if( $owner && $owner->user ) {
            $total_days_present_without_owner = $users->reduce(function($sum, $user) use ($owner) {
                return $sum + ($owner->user->id == $user->id ? 0 : $user->days_present);
            }, 0);
            Log::info("Total presence days without owner: $total_days_present_without_owner");
        } else {
            $total_days_present_without_owner = 0;
        }

        Log::info("{$users->count()} people in the community");
        if( $users->count() === 0 || $total_days_present === 0 ) return;

        $funds_tag = ExpenseTag::where('slug', 'funds');
        $compensation_tag = ExpenseTag::where('slug', 'compensation');

        $days_in_month = $date->copy()->startOfMonth()->diffInDays($date);
        if( $days_in_month == 0 ) {
            Log::info("cannot compute expenses on the month first day");
            return;
        }
        $total_shared_cost = $this->cost_per_month * $period_start->diffInDays($date) / $days_in_month;
        Log::info("Total shared cost: $total_shared_cost");

        if( $total_days_present_without_owner ) {
            // get the oldest date when the car could have been used by a non-owner
            $period_start_without_owner = $users->reduce(function($min_date,$user) use ($owner) {
                return $owner->user->id == $user->id ? $min_date : $min_date->minimum($user->borrower->approved_at)->startOfDay();
            }, $date);
            $period_start_without_owner = $period_start_without_owner->maximum($period_start);
            // compute owner compensation pro rata
            $total_owner_compensation = $this->owner_compensation * ($period_start_without_owner->diffInDays($date)+1) / ($days_in_month+1);
        } else {
            $total_owner_compensation = 0;
        }
        Log::info("Total owner compensation: $total_owner_compensation");

        foreach($users as $user) {
            $shared_cost = $total_shared_cost * $user->days_present / $total_days_present;
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
            if( $total_owner_compensation && $this->owner->user->id !== $user->id ){
                $owner_compensation = $total_owner_compensation * $user->days_present / $total_days_present_without_owner;
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
