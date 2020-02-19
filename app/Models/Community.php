<?php

namespace App\Models;

use App\Models\Loanable;
use App\Models\Pricing;
use App\Rules\Polygon;
use App\Utils\PointCast;
use App\Utils\PolygonCast;
use App\Transformers\CommunityTransformer;
use Illuminate\Database\Eloquent\Builder;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Vkovic\LaravelCustomCasts\HasCustomCasts;

class Community extends BaseModel
{
    use HasCustomCasts, PostgisTrait;

    public static $rules = [
        'name' => 'nullable',
        'description' => 'nullable',
        'area' => 'nullable',
        'type' => [
            'nullable',
            'in:private,neighborhood,borough',
        ],
    ];

    public static $filterTypes = [
        'id' => 'number',
        'name' => 'text',
        'type' => ['neighborhood', 'borough', 'private'],
    ];

    protected $fillable = [
        'area',
        'description',
        'name',
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

    public static function getRules($action, $auth = null) {
        return array_merge(
            static::$rules,
            [
                'area' => ['nullable', new Polygon]
            ]
        );
    }

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

    public $collections = ['users', 'pricings', 'loanables'];

    public $computed =  ['area_google', 'center_google'];

    public function memberships() {
        return $this->hasMany(Membership::class);
    }

    public function users() {
        return $this->belongsToMany(User::class)
            ->using(Pivots\CommunityUser::class)
            ->withTimestamps()
            ->withPivot(['id', 'approved_at', 'created_at', 'role', 'suspended_at', 'updated_at']);
    }

    public function pricings() {
        return $this->hasMany(Pricing::class);
    }

    public function loanables() {
        return $this->hasMany(Loanable::class);
    }

    public function getAreaGoogleAttribute() {
        if (!$this->area) {
            return null;
        }

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

    public function scopeAccessibleBy(Builder $query, $user) {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            return $q->whereHas('users', function ($q2) use ($user) {
                return $q2->where('users.id', $user->id);
            })->orWhere('type', '!=', 'private');
        });
    }
}
