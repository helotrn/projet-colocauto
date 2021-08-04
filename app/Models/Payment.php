<?php

namespace App\Models;

use Carbon\Carbon;

class Payment extends Action
{
    public static function boot()
    {
        parent::boot();

        self::saved(function ($model) {
            if ($model->executed_at) {
                return;
            }

            switch ($model->status) {
                case "completed":
                    $model->executed_at = Carbon::now();
                    $model->save();
                    break;
                case "canceled":
                    $model->executed_at = Carbon::now();
                    $model->save();
                    break;
            }
        });
    }

    public static function getColumnsDefinition()
    {
        return [
            "*" => function ($query = null) {
                if (!$query) {
                    return "payments.*";
                }

                return $query->selectRaw("payments.*");
            },
            "type" => function ($query = null) {
                if (!$query) {
                    return "'payment' AS type";
                }

                return $query->selectRaw("'payment' AS type");
            },
        ];
    }

    protected $fillable = ["loan_id"];

    public $readOnly = false;

    public $items = ["borrower_invoice", "owner_invoice", "loan"];

    public function borrowerInvoice()
    {
        return $this->belongsTo(Invoice::class, "borrower_invoice_id");
    }

    public function ownerInvoice()
    {
        return $this->belongsTo(Invoice::class, "owner_invoice_id");
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
