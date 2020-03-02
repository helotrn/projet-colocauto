<?php

namespace App\Models;

class Padlock extends BaseModel
{
    public static $rules = [
        'mac_address' => 'required',
    ];

    protected $fillable = [
        'mac_address',
    ];
}
