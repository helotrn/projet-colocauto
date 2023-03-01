<?php

namespace App\Models;

use App\Events\BorrowerApprovedEvent;
use App\Events\BorrowerCompletedEvent;
use App\Events\BorrowerSuspendedEvent;
use App\Models\Loan;
use App\Models\User;
use App\Casts\TimestampWithTimezoneCast;
use Illuminate\Database\Eloquent\SoftDeletes;

class Borrower extends BaseModel
{
    use SoftDeletes;

    public static function boot()
    {
        parent::boot();

        // For every save
        self::saved(function ($model) {
            // Stop all logic if the borrower is suspended
            if (!!$model->suspended_at) {
                $changes = $model->getChanges();
                if( array_key_exists("suspended_at", $changes) ) {
                    // the borrower has just been suspended
                    event(new BorrowerSuspendedEvent($model->user));
                }
                return;
            }

            if (!!$model->approved_at) {
                event(new BorrowerApprovedEvent($model->user));
            } elseif ($model->is_complete) {
                event(new BorrowerCompletedEvent($model->user));
            }
        });
    }

    public static $rules = [
        "drivers_license_number" => ["nullable"],
        "has_not_been_sued_last_ten_years" => ["boolean"],
        "noke_id" => ["nullable"],
        "approved_at" => ["nullable", "date"],
        "submitted_at" => ["nullable", "date"],
    ];

    protected $fillable = [
        "drivers_license_number",
        "has_not_been_sued_last_ten_years",
        "noke_id",
        "submitted_at",
        "user_id",
    ];

    protected $casts = [
        "approved_at" => TimestampWithTimezoneCast::class,
        "suspended_at" => TimestampWithTimezoneCast::class,
    ];

    public $items = ["user"];

    public $computed = ["approved", "is_complete", "suspended", "validated"];

    public $morphOnes = [
        "insurance" => "fileable",
    ];

    public $morphManys = [
        "saaq" => "fileable",
        "gaa" => "fileable",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public $collections = ["loans", "saaq", "gaa"];

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    // Has the borrower a completed profile?
    public function getIsCompleteAttribute()
    {
        return !!$this->drivers_license_number &&
            $this->has_not_been_sued_last_ten_years &&
            !!$this->gaa()->exists() &&
            !!$this->saaq()->exists();
    }

    public function getApprovedAttribute()
    {
        return !!$this->approved_at;
    }

    public function getSuspendedAttribute()
    {
        return !!$this->suspended_at;
    }

    public function getValidatedAttribute()
    {
        return $this->approved && !$this->suspended;
    }

    public function gaa()
    {
        return $this->morphMany(File::class, "fileable")->where("field", "gaa");
    }

    public function insurance()
    {
        return $this->morphOne(File::class, "fileable")->where(
            "field",
            "insurance"
        );
    }

    public function saaq()
    {
        return $this->morphMany(File::class, "fileable")->where(
            "field",
            "saaq"
        );
    }
}
