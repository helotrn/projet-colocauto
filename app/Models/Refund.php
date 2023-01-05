<?php

namespace App\Models;

use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Refund extends BaseModel
{
    use SoftDeletes;

    public static $rules = [
        "amount" => ["required","numeric","gt:0"],
        "executed_at" => ["nullable","date"],
    ];

    protected $fillable = ["amount", "executed_at", "user_id", "credited_user_id"];

    public $items = ["user", "credited_user"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creditedUser()
    {
        return $this->belongsTo(User::class, 'credited_user_id');
    }

    public static function boot()
    {
        parent::boot();

        self::saved(function ($model) {
            if (!$model->executed_at) {
                // default date is today
                $model->executed_at = Carbon::now();
                $model->save();
            }
        });
    }
}
