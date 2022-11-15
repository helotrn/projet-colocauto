<?php

namespace App\Models;

use App\Models\Community;
use App\Casts\TimestampWithTimezoneCast;

class Invitation extends BaseModel
{
    public static $rules = [
        "email" => ["required", "email"],
        "consumed_at" => ["nullable", "date"],
    ];

    protected $fillable = ["community_id", "email"];

    protected $hidden = [];

    public $items = ["community", "token"];

    protected $casts = [
        "consumed_at" => TimestampWithTimezoneCast::class,
    ];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function consume()
    {
        $this->consumed_at = new \DateTime();
        $this->save();
    }
}
