<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\ImageTransformer;

class Image extends BaseModel
{
    public static $rules = [
        'imageable_type' => 'nullable',
        'imageable_id' => 'nullable',
        'path' => 'required',
        'filename' => 'required',
        'original_filename' => 'required',
        'field' => 'nullable',
        'width' => 'required',
        'height' => 'required',
        'orientation' => 'required',
    ];
    
    protected $fillable = [
        'imageable_type',
        'imageable_id',
        'path',
        'filename',
        'original_filename',
        'field',
        'width',
        'height',
        'orientation',
    ];

    public static $transformer = ImageTransformer::class;
}
