<?php

namespace App\Models;

use App\Models\Community;

class Invitation extends BaseModel
{
    public static $rules = [
        "email" => ["required", "email"],
    ];

    protected $fillable = ["community_id", "email"];

    protected $hidden = [];

    public $items = ["community", "token"];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }
}
