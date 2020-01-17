<?php

namespace App\Models;

use App\Models\Pricing;
use App\Utils\PointCast;
use App\Utils\PolygonCast;
use App\Transformers\CommunityTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Vkovic\LaravelCustomCasts\HasCustomCasts;

class Community extends BaseModel
{
    use HasCustomCasts, PostgisTrait;

    public static $rules = [
        'name' => 'required',
        'description' => 'nullable',
        'area' => 'nullable',
        'type' => 'required|in:private,neighborhood,borough'
    ];

    protected $fillable = [
        'name',
        'description',
        'area',
        'type',
    ];

    protected $postgisFields = [
        'center',
        'area',
    ];

    protected $postgisTypes = [
        'center' => [
            'geomtype' => 'point',
        ],
        'area' => [
            'geomtype' => 'geography',
        ],
    ];

    protected $casts = [
        'center' => PointCast::class,
        'area' => PolygonCast::class,
    ];

    public static $transformer = CommunityTransformer::class;

    public static function getColumnsDefinition() {
        return [
            '*' => function ($query = null) {
                if (!$query) {
                    return 'communities.*';
                }

                return $query->selectRaw('communities.*');
            },
            'center' => function ($query = null) {
                if (!$query) {
                    return 'ST_Centroid(communities.area::geometry)';
                }

                return $query->selectRaw('ST_Centroid(communities.area::geometry) AS center');
            }
        ];
    }

    public $collections = ['users', 'pricings'];

    public $computed =  ['area_google', 'center_google'];

    public function users() {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivot(['role', 'created_at', 'updated_at']);
    }

    public function pricings() {
        return $this->hasMany(Pricing::class);
    }

    public function getAreaGoogleAttribute() {
        return array_map(function ($point) {
            return [
                'lat' => $point[0],
                'lng' => $point[1],
            ];
        }, $this->area);
    }

    public function getCenterGoogleAttribute() {
        return [
            'lat' => $this->center[0],
            'lng' => $this->center[1],
        ];
    }
}
