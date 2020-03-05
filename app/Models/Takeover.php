<?php

namespace App\Models;

use App\Models\Action;
use App\Models\Loan;

class Takeover extends Action
{
    protected $table = 'takovers';

    public static $rules = [
        'status' => 'required',
        'mileage_beginning' => 'required',
        'fuel_beginning' => 'required',
        'comments_on_vehicle' => 'nullable',
    ];

    protected $fillable = [
        'status',
        'mileage_beginning',
        'fuel_beginning',
        'comments_on_vehicle',
    ];

    public function loan() {
        return $this->belongsTo(Loan::class);
    }

    public static function boot() {
        parent::boot();

        self::saved(function ($model) {
            if (!$model->executed_at) {
                switch ($model->status) {
                    case 'completed':
                        $loanId = $model->loan->id;

                        $model->executed_at = Carbon::now();
                        $model->save();
                        break;
                    case 'canceled':
                        $model->executed_at = Carbon::now();
                        $model->save();
                        $model->loan->setCanceled();
                        break;
                }
            }
        });
    }

    public static function getColumnsDefinition() {
        return [
            '*' => function ($query = null) {
                if (!$query) {
                    return 'takeovers.*';
                }

                return $query->selectRaw('takeovers.*');
            },
            'type' => function ($query = null) {
                if (!$query) {
                    return "'takeover' AS type";
                }

                return $query->selectRaw("'takeover' AS type");
            }
        ];
    }
}
