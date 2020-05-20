<?php

namespace App\Models;

use App\Rules\Polygon;
use App\Utils\PointCast;
use App\Utils\PolygonCast;
use App\Transformers\CommunityTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Vkovic\LaravelCustomCasts\HasCustomCasts;

class Community extends BaseModel
{
    use HasCustomCasts, PostgisTrait, SoftDeletes;

    public static $rules = [
        'name' => 'required',
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

    public static $transformer = CommunityTransformer::class;

    public static function getRules($action = '', $auth = null) {
        switch ($action) {
            case 'destroy':
                return [];
            default:
                return array_merge(
                    static::$rules,
                    [
                        'area' => ['nullable', new Polygon]
                    ]
                );
        }
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
            },
        ];
    }

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

    public $collections = ['users', 'pricings', 'loanables'];

    public $computed =  ['area_google', 'center_google'];

    public function users() {
        return $this->belongsToMany(User::class)
            ->using(Pivots\CommunityUser::class)
            ->withTimestamps()
            ->withPivot(['id', 'approved_at', 'created_at', 'role', 'suspended_at', 'updated_at']);
    }

    public function pricings() {
        return $this->hasMany(Pricing::class)->orderBy('object_type', 'desc');
    }

    public function loanables() {
        return $this->hasMany(Loanable::class);
    }

    public function getPricingFor(Loanable $loanable) {
        return $this->pricings->where('object_type', $loanable->type)->first()
            ?: $this->pricings->where('object_type', null)->first();
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

    public function scopeSearch(Builder $query, $q) {
        if (!$q) {
            return $query;
        }

        return $query
            ->where(
                \DB::raw('unaccent(name)'),
                'ILIKE',
                \DB::raw("unaccent('%$q%')")
            );
    }

    public function scopeFor(Builder $query, $for, $user) {
        if (!$user) {
            $for = 'read';
        }

        if ($user->isAdmin()) {
            return $query;
        }

        switch ($for) {
            case 'edit':
                return $query
                    ->whereHas('users', function ($q) use ($user) {
                        return $q->where('community_user.id', $user->id)
                            ->where('community_user.role', 'admin');
                    })
                    ->orWhereHas('users', function ($q) use ($user) {
                        return $q->where('users.id', $user->id)
                            ->where('users.role', 'admin');
                    });
            case 'read':
            default:
                return $query;
        }
    }
}
