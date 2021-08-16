<?php

namespace App\Models;

use App\Models\Pivots\CommunityUser;
use App\Models\User;
use Auth;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Laravel\Passport\Token;
use Storage;

class Image extends BaseModel
{
    public static $rules = [
        "imageable_type" => "nullable",
        "imageable_id" => "nullable",
        "path" => "required",
        "filename" => "required",
        "original_filename" => "required",
        "field" => "nullable",
        "width" => "required",
        "height" => "required",
        "orientation" => "required",
    ];

    protected $fillable = [
        "field",
        "filename",
        "filesize",
        "height",
        "imageable_id",
        "imageable_type",
        "original_filename",
        "path",
        "width",
    ];

    public static $sizes = [
        "thumbnail" => "256x@fit",
    ];

    public static $sizesByField = [];

    public static function fetch($path)
    {
        try {
            $file = Storage::disk("s3")->get($path);
        } catch (FileNotFoundException $e) {
            return null;
        }
        $manager = new ImageManager(["driver" => "imagick"]);
        return $manager->make($file);
    }

    public static function store($path, $image)
    {
        $image->stream();
        return Storage::disk("s3")->put($path, $image->__toString());
    }

    public static function copy($source, $destination)
    {
        $image = self::fetch($source);
        if (!$image) {
            return null;
        }
        return self::store($destination, $image);
    }

    public static function boot()
    {
        parent::boot();

        self::saving(function ($model) {
            if ($model->imageable) {
                $sizes = $model->imageable::$sizes;

                if (isset($model->imageable::$sizesByField[$model->field])) {
                    $sizes = array_merge(
                        $model->imageable::$sizesByField[$model->field],
                        $sizes
                    );
                }

                $imagePath = str_replace(
                    "tmp",
                    strtolower(
                        (new \ReflectionClass(
                            $model->imageable
                        ))->getShortName()
                    ),
                    $model->path
                );
            } else {
                $sizes = self::$sizes;
                $imagePath = $model->path;
            }

            $ds = DIRECTORY_SEPARATOR;
            $fullPath = $model->path . $ds . $model->filename;

            foreach ($sizes as $name => $size) {
                // Custom image generation
                if (strpos($size, "@") === false) {
                    $canvas = $model->imageable->{$size}($fullPath);

                    if (!$canvas) {
                        continue;
                    }
                } else {
                    // Generic image generation
                    $properties = explode("@", $size);

                    $dimensions = explode("x", array_shift($properties));

                    $type = array_pop($properties) ?: "fit";

                    $canvas = Image::fetch($fullPath);

                    if (!$canvas) {
                        continue;
                    }

                    if ($type === "fit") {
                        $width =
                            isset($dimensions[0])
                                ? $dimensions[0]
                                : null ?:
                            null;
                        $height =
                            isset($dimensions[1])
                                ? $dimensions[1]
                                : null ?:
                            null;

                        $canvas->resize($width, $height, function (
                            $constraint
                        ) {
                            $constraint->aspectRatio();
                        });
                    } elseif ($type === "crop") {
                        $width =
                            isset($dimensions[0])
                                ? $dimensions[0]
                                : null ?:
                            null;
                        $height =
                            isset($dimensions[1])
                                ? $dimensions[1]
                                : null ?:
                            null;

                        $x = $model->imageable->crop_x;
                        $y = $model->imageable->crop_y;

                        $origHeight = $canvas->height();
                        $origWidth = $canvas->width();

                        if ($origHeight / $origWidth <= 1) {
                            $cropHeight = $origHeight;
                            $cropWidth = intval(($origHeight * 2) / 3);
                        } else {
                            $cropWidth = $origWidth;
                            $cropHeight = intval($origWidth * 1.5);
                        }

                        $canvas->crop($cropWidth, $cropHeight, $x, $y);

                        if ($width || $height) {
                            $canvas->resize($width, $height, function (
                                $constraint
                            ) {
                                $constraint->aspectRatio();
                            });
                        }
                    }

                    if (
                        $model->imageable &&
                        method_exists($model->imageable, "insertImageWatermark")
                    ) {
                        $canvas = $model->imageable->insertImageWatermark(
                            $canvas,
                            $model->imageable
                        );
                    }
                }

                $targetPath = $imagePath . $ds . $name . "_" . $model->filename;
                Image::store($targetPath, $canvas);
            }

            Image::copy($fullPath, $imagePath . $ds . $model->filename);
            $model->path = $imagePath;
        });
    }

    protected $hidden = ["imageable", "imageable_type", "imageable_id"];

    protected $appends = ["sizes", "url"];

    public function imageable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(
            User::class,
            "imageable_id"
        )->whereImageableType(User::class);
    }

    public function communityUser()
    {
        return $this->belongsTo(
            CommunityUser::class,
            "imageable_id"
        )->whereImageableType(CommunityUser::class);
    }

    public function getSizesAttribute()
    {
        $base = env("BACKEND_URL_FROM_BROWSER");

        $sizes = [
            "original" => "{$base}{$this->attributes["path"]}/{$this->attributes["filename"]}",
        ];

        if ($this->imageable) {
            if (
                $this->imageable &&
                isset($this->imageable::$sizesByField[$this->field])
            ) {
                $sizes = array_merge(
                    $this->imageable::$sizesByField[$this->field],
                    $this->imageable::$sizes
                );
            } else {
                $sizes = $this->imageable::$sizes;
            }
        } else {
            $sizes = self::$sizes;
        }

        $tokenQueryString = $this->getTokenQueryString();

        $encodedFilename = str_replace(
            " ",
            "%20",
            $this->attributes["filename"]
        );
        foreach (array_keys($sizes) as $name) {
            $sizes[$name] =
                "$base{$this->attributes["path"]}/{$name}_{$encodedFilename}" .
                $tokenQueryString;
        }

        return $sizes;
    }

    public function getUrlAttribute()
    {
        $tokenQueryString = $this->getTokenQueryString();
        $appUrl = env("BACKEND_URL_FROM_BROWSER");

        return "{$appUrl}{$this->path}/{$this->filename}" . $tokenQueryString;
    }

    public function scopeAccessibleBy(Builder $query, $user)
    {
        if (!$user) {
            return $query->where(\DB::raw("1 = 0"));
        }

        if ($user->isAdmin()) {
            return $query;
        }

        // Image is...
        return $query
            // ...associated to a user
            ->where(function ($q) use ($user) {
                return $q->whereHas("user", function ($q) use ($user) {
                    return $q->accessibleBy($user);
                });
            })
            // ...or associated to a community user
            ->orWhere(function ($q) use ($user) {
                return $q->whereHas("communityUser", function ($q) use ($user) {
                    return $q->accessibleBy($user);
                });
            })
            // ...or a temporary file
            ->orWhere(function ($q) {
                return $q->whereImageableType(null);
            });
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
}
