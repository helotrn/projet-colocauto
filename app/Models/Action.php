<?php

namespace App\Models;

use App\Models\Loan;
use App\Models\User;
use App\Casts\TimestampWithTimezoneCast;
use Illuminate\Database\Eloquent\SoftDeletes;

class Action extends BaseModel
{
    use SoftDeletes;

    protected $casts = [
        "executed_at" => TimestampWithTimezoneCast::class,
    ];

    public $readOnly = true;

    public $items = ["loan", "user"];

    public function __construct(array $attributes = [])
    {
        // Set default attributes for all actions.
        // See this post on the two methods to do so (access 2022-05-11):
        //     https://stackoverflow.com/questions/18747500/how-to-set-a-default-attribute-value-for-a-laravel-eloquent-model
        $this->setRawAttributes(
            array_merge($this->attributes, [
                "status" => "in_process",
            ]),
            true
        );

        parent::__construct($attributes);
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
