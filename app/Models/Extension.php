<?php

namespace App\Models;

use Carbon\Carbon;

class Extension extends Action
{
    public static $rules = [
        'status' => 'required',
        'new_duration' => 'required',//add validation
        'comments_on_extension' => 'required|string',
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
                    return 'extensions.*';
                }

                return $query->selectRaw('extensions.*');
            },
            'type' => function ($query = null) {
                if (!$query) {
                    return "'extension' AS type";
                }

                return $query->selectRaw("'extension' AS type");
            }
        ];
    }

    public $readOnly = false;

    protected $fillable = [
        'status',
        'new_duration',
        'comments_on_extension',
    ];

    public function loan() {
        return $this->belongsTo(Loan::class);
    }
}
