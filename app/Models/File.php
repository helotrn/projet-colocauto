<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\FileTransformer;

class File extends BaseModel
{
    public static $rules = [
        'fileable_type' => 'nullable',
        'fileable_id' => 'nullable',
        'path' => 'required',
        'filename' => 'required',
        'original_filename' => 'required',
        'filesize' => 'required',
        'field' => 'nullable',
    ];
    
    protected $fillable = [
        'fileable_type',
        'fileable_id',
        'path',
        'filename',
        'original_filename',
        'filesize',
        'field',
    ];

    public static $transformer = FileTransformer::class;

    public $belongsTo = ['user'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
