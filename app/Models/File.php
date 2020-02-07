<?php

namespace App\Models;

use App\Models\User;
use App\Transformers\FileTransformer;
use Illuminate\Database\Eloquent\Builder;
use Storage;

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

    public static $transformer = FileTransformer::class;

    protected $fillable = [
        'field',
        'fileable_id',
        'fileable_type',
        'filename',
        'filesize',
        'original_filename',
        'path',
    ];

    protected $hidden = ['fileable', 'fileable_type', 'fileable_id'];

    protected $appends = ['url'];

    public static function fetch($path) {
        $disk = app()->environment() === 'local' ? 'local' : 's3';
        try {
            $file = Storage::disk($disk)->get($path);
        } catch (FileNotFoundException $e) {
            return null;
        }
        return $file;
    }

    public static function store($path, $file) {
        $disk = app()->environment() === 'local' ? 'local' : 's3';
        return Storage::disk($disk)->put($path, $file);
    }

    public static function copy($source, $destination) {
        $file = static::fetch($source);
        if (!$file) {
            return null;
        }
        return static::store($destination, $file);
    }

    public static function boot() {
        parent::boot();

        self::saving(function ($model) {
            $ds = DIRECTORY_SEPARATOR;
            $fullPath = $model->path . $ds . $model->filename;

            if ($model->fileable) {
                $filePath = str_replace(
                    'tmp',
                    strtolower((new \ReflectionClass($model->fileable))->getShortName()),
                    $model->path
                );
            } else {
                $filePath = $model->path;
            }

            File::copy($fullPath, $filePath . $ds . $model->filename);
            $model->path = $filePath;
        });
    }

    public $belongsTo = ['user'];

    public function fileable() {
        return $this->morphTo();
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute() {
        $base = app()->environment() === 'local'
            ? app()->make('url')->to('/')
            : env('CDN_URL');
        return "{$base}{$this->path}/{$this->filename}";
    }
}
