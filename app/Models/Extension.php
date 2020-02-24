<?php

namespace App\Models;

use App\Models\Action;
use App\Models\Loan;
use App\Transformers\ExtensionTransformer;

class Extension extends Action
{
    protected $table = 'extensions';

    public static $rules = [
        'status' => 'required',
        'new_duration' => 'required',//add validation
        'comments_on_extension' => 'required|string',
    ];

    protected $fillable = [
        'status',
        'new_duration',
        'comments_on_extension',
    ];

    public static $transformer = ExtensionTransformer::class;

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
}
