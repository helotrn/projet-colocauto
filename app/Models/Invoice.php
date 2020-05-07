<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends BaseModel
{
    use SoftDeletes;

    public static $rules = [
        'period' => 'required',
    ];

    public static $filterTypes = [
      'created_at' => 'date',
      'paid_at' => 'date',
      'user.full_name' => 'text'
    ];

    public static function getColumnsDefinition() {
        return [
            '*' => function ($query = null) {
                if (!$query) {
                    return 'invoices.*';
                }

                return $query->selectRaw('invoices.*');
            },
            'items_count' => function ($query = null) {
                if (!$query) {
                    return 'count(bill_items_join.id)';
                }

                $query = static::addJoin(
                    $query,
                    'bill_items AS bill_items_join',
                    \DB::raw('bill_items_join.invoice_id'),
                    '=',
                    \DB::raw('invoices.id')
                );

                return $query
                    ->selectRaw('count(bill_items_join.id) AS items_count')
                    ->groupBy('invoices.id');
            },
            'total' => function ($query = null) {
                if (!$query) {
                    return 'sum(bill_items_join.amount)';
                }

                $query = static::addJoin(
                    $query,
                    'bill_items AS bill_items_join',
                    \DB::raw('bill_items_join.invoice_id'),
                    '=',
                    \DB::raw('invoices.id')
                );

                return $query
                    ->selectRaw('sum(bill_items_join.amount) AS total')
                    ->groupBy('invoices.id');
            },
            'total_tps' => function ($query = null) {
                if (!$query) {
                    return 'sum(bill_items_join.taxes_tps)::decimal(8, 2)';
                }

                $query = static::addJoin(
                    $query,
                    'bill_items AS bill_items_join',
                    \DB::raw('bill_items_join.invoice_id'),
                    '=',
                    \DB::raw('invoices.id')
                );

                return $query
                    ->selectRaw('sum(bill_items_join.taxes_tps)::decimal(8, 2) AS total_tps')
                    ->groupBy('invoices.id');
            },
            'total_tvq' => function ($query = null) {
                if (!$query) {
                    return 'sum(bill_items_join.taxes_tvq)::decimal(8, 2)';
                }

                $query = static::addJoin(
                    $query,
                    'bill_items AS bill_items_join',
                    \DB::raw('bill_items_join.invoice_id'),
                    '=',
                    \DB::raw('invoices.id')
                );

                return $query
                    ->selectRaw('sum(bill_items_join.taxes_tvq)::decimal(8, 2) AS tvq')
                    ->groupBy('invoices.id');
            },
            'total_with_taxes' => function ($query = null) {
                $defs = static::getColumnsDefinition();
                $total = $defs['total']();
                $tps = $defs['total_tps']();
                $tvq = $defs['total_tvq']();

                if (!$query) {
                    return "($total + $tps + $tvq)::decimal(8, 2)";
                }

                $query = static::addJoin(
                    $query,
                    'bill_items AS bill_items_join',
                    \DB::raw('bill_items_join.invoice_id'),
                    '=',
                    \DB::raw('invoices.id')
                );

                return $query
                    ->selectRaw("($total + $tps + $tvq)::decimal(8, 2) AS total_with_taxes")
                    ->groupBy('invoices.id');
            },
        ];
    }

    protected $fillable = [
        'period',
    ];

    public $items = ['user'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public $collections = ['bill_items'];

    public function billItems() {
        return $this->hasMany(BillItem::class);
    }

    public function scopeAccessibleBy(Builder $query, $user) {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->whereUserId($user->id);
    }

    public function pay() {
        $this->paid_at = new \DateTime;
        $this->save();
    }
}
