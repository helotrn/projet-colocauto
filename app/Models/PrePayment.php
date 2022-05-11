<?php

namespace App\Models;

use Carbon\Carbon;

class PrePayment extends Action
{
    public static function getColumnsDefinition()
    {
        return [
            "*" => function ($query = null) {
                if (!$query) {
                    return "pre_payments.*";
                }

                return $query->selectRaw("pre_payments.*");
            },
            "type" => function ($query = null) {
                if (!$query) {
                    return "'pre_payment' AS type";
                }

                return $query->selectRaw("'pre_payment' AS type");
            },
        ];
    }

    public $readOnly = false;

    protected $fillable = [];

    public $items = ["loan"];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function complete($at = null)
    {
        $this->executed_at = new Carbon($at);
        $this->status = "completed";

        return $this;
    }

    public function isCompleted()
    {
        return $this->status == "completed";
    }

    public function cancel($at = null)
    {
        $this->executed_at = new Carbon($at);
        $this->status = "canceled";

        return $this;
    }

    public function isCanceled()
    {
        return $this->status == "canceled";
    }
}
