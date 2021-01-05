<?php

namespace App\Models;

use Carbon\Carbon;

class Handover extends Action
{
    public static $rules = [
        'status' => 'required',
        'mileage_end' => 'required',
        'comments_by_borrower' => 'nullable',
        'comments_by_owner' => 'nullable',
        'purchases_amount' => 'required',
    ];

    public static $sizes = [
        'thumbnail' => '256x@fit',
    ];

    protected $fillable = [
        'mileage_end',
        'comments_by_borrower',
        'comments_by_owner',
        'purchases_amount',
    ];

    public $readOnly = false;

    public $morphOnes = [
        'image' => 'imageable',
        'expense' => 'imageable',
    ];

    public function expense() {
        return $this->morphOne(Image::class, 'imageable')->where('field', 'expense');
    }

    public function image() {
        return $this->morphOne(Image::class, 'imageable')->where('field', 'image');
    }

    public function loan() {
        return $this->belongsTo(Loan::class);
    }

    public static function boot() {
        parent::boot();

        self::saved(function ($model) {
            if ($model->executed_at) {
                return;
            }

            switch ($model->status) {
                case 'completed':
                    if (!$model->loan->payment) {
                        $payment = new Payment();
                        $payment->loan()->associate($model->loan);
                        $payment->save();
                    }

                    $model->executed_at = Carbon::now();
                    $model->save();
                    break;
                default:
                    break;
            }
        });
    }

    public static function getColumnsDefinition() {
        return [
            '*' => function ($query = null) {
                if (!$query) {
                    return 'handovers.*';
                }

                return $query->selectRaw('handovers.*');
            },
            'type' => function ($query = null) {
                if (!$query) {
                    return "'handover' AS type";
                }

                return $query->selectRaw("'handover' AS type");
            }
        ];
    }
}
