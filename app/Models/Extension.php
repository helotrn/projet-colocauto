<?php

namespace App\Models;

use Carbon\Carbon;

class Extension extends Action
{
    public static $rules = [
        "status" => "required",
        "new_duration" => "required",
        "comments_on_extension" => "required|string",
        "loan_id" => "extendable",
    ];

    public static function boot()
    {
        parent::boot();

        self::saved(function ($model) {
            if (!$model->executed_at) {
                switch ($model->status) {
                    // Complete (meaning to accept) extension if loanable is self-service.
                    case "in_process":
                        if (
                            $model->loan &&
                            (!$model->loan->loanable->owner ||
                                $model->loan->loanable->is_self_service)
                        ) {
                            $model->status = "completed";
                            $model->executed_at = Carbon::now();
                            $model->save();
                        }
                        break;
                    case "completed":
                    case "canceled":
                    case "rejected":
                        $model->executed_at = Carbon::now();
                        $model->save();
                        break;
                }
            }
        });
    }

    public static function getColumnsDefinition()
    {
        return [
            "*" => function ($query = null) {
                if (!$query) {
                    return "extensions.*";
                }

                return $query->selectRaw("extensions.*");
            },
            "type" => function ($query = null) {
                if (!$query) {
                    return "'extension' AS type";
                }

                return $query->selectRaw("'extension' AS type");
            },
        ];
    }

    public $computed = ["type"];

    public $readOnly = false;

    protected $fillable = [
        "comments_on_extension",
        "loan_id",
        "new_duration",
        "status",
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function getTypeAttribute()
    {
        return "extension";
    }
}
