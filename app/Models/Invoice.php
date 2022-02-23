<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends BaseModel
{
    use SoftDeletes;

    public static $rules = [
        "period" => "required",
        "user_id" => "required",
    ];

    public static $filterTypes = [
        "created_at" => "date",
        "paid_at" => "date",
        "user.full_name" => "text",
    ];

    public static function getColumnsDefinition()
    {
        return [
            "*" => function ($query = null) {
                if (!$query) {
                    return "invoices.*";
                }

                return $query->selectRaw("invoices.*");
            },

            "user_full_name" => function ($query = null) {
                if (!$query) {
                    return "CONCAT(users.name, ' ', users.last_name)";
                }

                $query->selectRaw(
                    "CONCAT(users.name, ' ', users.last_name)" .
                        " AS user_full_name"
                );

                $query = static::addJoin(
                    $query,
                    "users",
                    "users.id",
                    "=",
                    "invoices.user_id"
                );

                $query->groupBy("users.id");

                return $query;
            },

            "items_count" => function ($query = null) {
                if (!$query) {
                    return "count(bill_items_join.id)";
                }

                $query = static::addJoin(
                    $query,
                    "bill_items AS bill_items_join",
                    \DB::raw("bill_items_join.invoice_id"),
                    "=",
                    \DB::raw("invoices.id")
                );

                return $query
                    ->selectRaw("count(bill_items_join.id) AS items_count")
                    ->groupBy("invoices.id");
            },
            "total" => function ($query = null) {
                if (!$query) {
                    return "sum(bill_items_join.amount)";
                }

                $query = static::addJoin(
                    $query,
                    "bill_items AS bill_items_join",
                    \DB::raw("bill_items_join.invoice_id"),
                    "=",
                    \DB::raw("invoices.id")
                );

                return $query
                    ->selectRaw("sum(bill_items_join.amount) AS total")
                    ->groupBy("invoices.id");
            },
            "total_tps" => function ($query = null) {
                if (!$query) {
                    return "sum(bill_items_join.taxes_tps)::decimal(8, 2)";
                }

                $query = static::addJoin(
                    $query,
                    "bill_items AS bill_items_join",
                    \DB::raw("bill_items_join.invoice_id"),
                    "=",
                    \DB::raw("invoices.id")
                );

                return $query
                    ->selectRaw(
                        "sum(bill_items_join.taxes_tps)::decimal(8, 2) AS total_tps"
                    )
                    ->groupBy("invoices.id");
            },
            "total_tvq" => function ($query = null) {
                if (!$query) {
                    return "sum(bill_items_join.taxes_tvq)::decimal(8, 2)";
                }

                $query = static::addJoin(
                    $query,
                    "bill_items AS bill_items_join",
                    \DB::raw("bill_items_join.invoice_id"),
                    "=",
                    \DB::raw("invoices.id")
                );

                return $query
                    ->selectRaw(
                        "sum(bill_items_join.taxes_tvq)::decimal(8, 2) AS total_tvq"
                    )
                    ->groupBy("invoices.id");
            },
            "total_with_taxes" => function ($query = null) {
                $defs = static::getColumnsDefinition();
                $total = $defs["total"]();
                $tps = $defs["total_tps"]();
                $tvq = $defs["total_tvq"]();

                if (!$query) {
                    return "($total + $tps + $tvq)::decimal(8, 2)";
                }

                $query = static::addJoin(
                    $query,
                    "bill_items AS bill_items_join",
                    \DB::raw("bill_items_join.invoice_id"),
                    "=",
                    \DB::raw("invoices.id")
                );

                return $query
                    ->selectRaw(
                        "($total + $tps + $tvq)::decimal(8, 2) AS total_with_taxes"
                    )
                    ->groupBy("invoices.id");
            },
        ];
    }

    public static function formatAmountForDisplay($amount)
    {
        return number_format($amount, 2, ",", " ");
    }

    protected $fillable = ["period"];

    public $items = ["payment_method", "user"];

    public $computed = [
        "items_count",
        "total",
        "total_tps",
        "total_tvq",
        "total_with_taxes",
    ];

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public $collections = ["bill_items"];

    public function billItems()
    {
        return $this->hasMany(BillItem::class);
    }

    public function scopeAccessibleBy(Builder $query, $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->whereUserId($user->id);
    }

    public function pay()
    {
        $this->paid_at = new \DateTime();
        $this->save();
    }

    public function payWith($paymentMethod)
    {
        $this->paymentMethod()->associate($paymentMethod);
        $this->paid_at = new \DateTime();
        $this->save();
    }

    public function getTotalTpsAttribute()
    {
        if (isset($this->attributes["total_tps"])) {
            return $this->attributes["total_tps"];
        }

        return $this->billItems->sum("taxes_tps");
    }

    public function getTotalTvqAttribute()
    {
        if (isset($this->attributes["total_tvq"])) {
            return $this->attributes["total_tvq"];
        }

        return $this->billItems->sum("taxes_tvq");
    }

    public function getTotalWithTaxesAttribute()
    {
        if (isset($this->attributes["total_with_taxes"])) {
            return $this->attributes["total_with_taxes"];
        }

        return $this->total + $this->total_tps + $this->total_tvq;
    }

    public function getTotalAttribute()
    {
        if (isset($this->attributes["total"])) {
            return $this->attributes["total"];
        }

        return $this->billItems->sum("amount");
    }

    public function getItemsCountAttribute()
    {
        if (isset($this->attributes["items_count"])) {
            return $this->attributes["items_count"];
        }

        return $this->billItems->count();
    }
}
