<?php

namespace App\Models;

use App\Models\Action;
use App\Models\Loan;
use Carbon\Carbon;

class Incident extends Action
{
    public static $rules = [
        'incident_type' => 'required',
    ];

    protected $fillable = [
        'incident_type',
        'comments_on_incident',
    ];

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
                        break;
                }
            }
        });
    }

    public static function getColumnsDefinition() {
        return [
            '*' => function ($query = null) {
                if (!$query) {
                    return 'incidents.*';
                }

                return $query->selectRaw('incidents.*');
            },
            'type' => function ($query = null) {
                if (!$query) {
                    return "'incident' AS type";
                }

                return $query->selectRaw("'incident' AS type");
            }
        ];
    }

    public $readOnly = false;

    public function loan() {
        return $this->belongsTo(Loan::class);
    }
}
