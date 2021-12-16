<?php

namespace App\Models;

use App\Models\Borrower;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Passport\Token;
use Storage;
use Log;

class File extends BaseModel
{
    public static $rules = [
        "fileable_type" => "nullable",
        "fileable_id" => "nullable",
        "path" => "required",
        "filename" => "required",
        "original_filename" => "required",
        "filesize" => "required",
        "field" => "nullable",
    ];

    protected $fillable = [
        "field",
        "fileable_id",
        "fileable_type",
        "filename",
        "filesize",
        "original_filename",
        "path",
    ];

    protected $hidden = ["fileable", "fileable_type", "fileable_id"];

    protected $appends = ["url"];

    public static function fetch($path)
    {
        try {
            $file = Storage::disk(env("FILESYSTEM_DRIVER"))->get($path);
        } catch (FileNotFoundException $e) {
            return null;
        }
        return $file;
    }

    public static function store($path, $file)
    {
        return Storage::disk(env("FILESYSTEM_DRIVER"))->putFileAs(
            dirname($path),
            $file,
            basename($path)
        );
    }

    public static function copy($source, $destination)
    {
        if ($source === $destination) {
            return true;
        }
        return Storage::disk(env("FILESYSTEM_DRIVER"))->copy(
            $source,
            $destination
        );
    }

    public static function boot()
    {
        parent::boot();

        self::saving(function ($model) {
            $ds = DIRECTORY_SEPARATOR;
            $fullPath = $model->path . $ds . $model->filename;

            if ($model->fileable) {
                $filePath = str_replace(
                    "tmp",
                    strtolower(
                        (new \ReflectionClass($model->fileable))->getShortName()
                    ),
                    $model->path
                );
            } else {
                $filePath = $model->path;
            }

            File::copy($fullPath, $filePath . $ds . $model->filename);

            $model->path = $filePath;

            // Notify the admins of a newly uploaded document
            if ($model->borrower()) {
                // if (
                //     $borrower->getIsCompleteAttribute() &&
                //     ($model->field == "saaq" || $model->field == "gaa")
                // ) {
                //     // Send a notification
                // }
            }
        });
    }

    public $items = ["user"];

    public function fileable()
    {
        return $this->morphTo();
    }

    public function borrower()
    {
        return $this->belongsTo(
            Borrower::class,
            "id",
            "fileable_id"
        )->whereFileableType(Borrower::class);
    }

    public function getUrlAttribute()
    {
        $tokenQueryString = $this->getTokenQueryString();
        $appUrl = env("BACKEND_URL_FROM_BROWSER");

        // We check the presence of the /storage prefix for compatibility with old data
        // We add it if it is not there
        if (preg_match('/^\/storage\/.*$/m', $this->path)) {
            return "{$appUrl}{$this->path}/{$this->filename}" .
                $tokenQueryString;
        }
        return "{$appUrl}/storage{$this->path}/{$this->filename}" .
            $tokenQueryString;
    }

    protected function getTokenQueryString()
    {
        if ($user = Auth::user()) {
            $token = Token::whereUserId($user->id)
                ->whereRevoked(false)
                ->orderBy("expires_at", "desc")
                ->limit(1)
                ->first();
            if ($token) {
                return "?token=$token->id";
            }
        }

        return "";
    }

    public function scopeAccessibleBy(Builder $query, $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }

        // File is...
        return $query
            // ...associated to a borrower
            ->where(function ($q) use ($user) {
                return $q->whereHas("borrower", function ($q) use ($user) {
                    return $q->whereHas("user", function ($q) use ($user) {
                        return $q->accessibleBy($user);
                    });
                });
            })
            // ...or a temporary file
            ->orWhere(function ($q) {
                return $q->whereFileableType(null);
            });
    }
}
