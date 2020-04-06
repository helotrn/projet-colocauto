<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Padlock extends BaseModel
{
    public static $rules = [
        'external_id' => 'required',
        'mac_address' => 'required',
        'name' => 'required',
    ];

    public static $filterTypes = [
        'name' => 'text',
        'loanable.name' => 'text',
    ];

    public static function boot() {
        parent::boot();

        self::saved(function ($model) {
            if ($model->loanable) {
                $newType = 'App\\Models\\' . ucfirst($model->loanable->type);

                if ($model->loanable_type !== $newType) {
                    $model->loanable_type = $newType;
                    $model->save();
                }
            }
        });
    }

    protected $fillable = [
        'external_id',
        'mac_address',
        'name',
    ];

    public $morphOnes = [
        'bike' => 'loanable',
        'trailer' => 'loanable',
    ];

    public $items = ['loanable'];

    public function loanable() {
        return $this->belongsTo(Loanable::class);
    }

    public function bike() {
        return $this->morphOne(Bike::class, 'loanable');
    }

    public function trailer() {
        return $this->morphOne(Trailer::class, 'loanable');
    }

    public function accessibleBy(Builder $query, $user) {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where('1 = 0');
    }
}
