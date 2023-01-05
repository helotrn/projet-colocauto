<?php

namespace App\Models;

class ExpenseTag extends BaseModel
{
    public static $rules = [
        "name" => "required",
    ];

    protected $fillable = ["name", "slug", "color"];

    public $collections = ["expenses"];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public static function boot()
    {
        parent::boot();

        self::saved(function ($model) {
            if (!$model->color) {
                $model->color = 'primary';
                $model->save();
            }
            if (!$model->slug) {
                $model->slug = strtolower(preg_replace('/[^\w]+/', '_', $model->name));
                $model->save();
            }
        });
    }
}
