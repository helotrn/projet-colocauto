<?php

namespace App\Models;

use App\Models\Action;
use App\Models\Loan;
use Carbon\Carbon;

class Takeover extends Action
{
    public static $rules = [
        'status' => 'required',
        'mileage_beginning' => [ 'nullable' ],
        'fuel_beginning' => [ 'nullable' ],
        'comments_on_vehicle' => [ 'nullable' ],
    ];

    protected $fillable = [
        'loan_id',
        'status',
        'mileage_beginning',
        'fuel_beginning',
        'comments_on_vehicle',
    ];

    public $morphOnes = [
        'image' => 'imageable',
    ];

    public function image() {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function loan() {
        return $this->belongsTo(Loan::class);
    }

    public static function boot() {
        parent::boot();

        self::saved(function ($model) {
            if (!$model->executed_at) {
                $model->executed_at = Carbon::now();

                switch ($model->status) {
                    case 'completed':
                        if (!$model->loan->handover) {
                            $handover = Handover::create([ 'loan_id' => $model->loan->id ]);
                            $model->loan->handover()->save($handover);
                        }
                        break;
                    case 'canceled':
                        $model->loan->setCanceled();
                        break;
                }
            }

            $model->save();
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
