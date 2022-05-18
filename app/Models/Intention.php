<?php

namespace App\Models;

use Carbon\Carbon;

class Intention extends Action
{
    public static function getColumnsDefinition()
    {
        return [
            "*" => function ($query = null) {
                if (!$query) {
                    return "intentions.*";
                }

                return $query->selectRaw("intentions.*");
            },
            "type" => function ($query = null) {
                if (!$query) {
                    return "'intention' AS type";
                }

                return $query->selectRaw("'intention' AS type");
            },
        ];
    }

    protected $fillable = ["message_for_borrower"];

    public $readOnly = false;

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
