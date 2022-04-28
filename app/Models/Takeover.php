<?php

namespace App\Models;

use Carbon\Carbon;

class Takeover extends Action
{
    public static $rules = [
        "status" => "required",
        "mileage_beginning" => ["nullable"],
        "comments_on_vehicle" => ["nullable"],
    ];

    public static $sizes = [
        "thumbnail" => "256x@fit",
    ];

    public static function boot()
    {
        parent::boot();

        self::saved(function ($model) {
            if ($model->executed_at) {
                return;
            }

            switch ($model->status) {
                case "completed":
                    if (!$model->loan->handover) {
                        $handover = new Handover();
                        $handover->loan()->associate($model->loan);
                        $handover->save();
                    }

                    $model->executed_at = Carbon::now();
                    $model->save();
                    break;
                case "canceled":
                    $model->executed_at = Carbon::now();
                    $model->save();
                    break;
                default:
                    break;
            }
        });
    }

    public static function getColumnsDefinition()
    {
        return [
            "*" => function ($query = null) {
                if (!$query) {
                    return "takeovers.*";
                }

                return $query->selectRaw("takeovers.*");
            },
            "type" => function ($query = null) {
                if (!$query) {
                    return "'takeover' AS type";
                }

                return $query->selectRaw("'takeover' AS type");
            },
        ];
    }

    protected $fillable = ["mileage_beginning", "comments_on_vehicle"];

    public $readOnly = false;

    public $morphOnes = [
        "image" => "imageable",
    ];

    public function image()
    {
        return $this->morphOne(Image::class, "imageable");
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function isContested()
    {
        return $this->status == "canceled";
    }
}
