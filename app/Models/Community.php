<?php

namespace App\Models;

use App\Rules\Polygon;
use App\Casts\PointCast;
use App\Casts\PolygonCast;
use App\Transformers\CommunityTransformer;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use Molotov\Traits\TreeScopes;
use MStaack\LaravelPostgis\Eloquent\PostgisTrait;

class Community extends BaseModel
{
    use PostgisTrait, SoftDeletes, TreeScopes;

    public static $rules = [
        'name' => 'required',
        'chat_group_url' => 'nullable',
        'description' => 'nullable',
        'long_description' => 'nullable',
        'area' => 'nullable',
        'type' => [
            'nullable',
            'in:private,neighborhood,borough',
        ],
        'parent_id' => [
            'nullable',
            'exists:communities,id',
            'different:id',
        ],
    ];

    public static $filterTypes = [
        'id' => 'number',
        'name' => 'text',
        'type' => ['neighborhood', 'borough', 'private'],
        'parent.name' => 'text',
    ];

    public static $transformer = CommunityTransformer::class;

    public static function getRules($action = '', $auth = null) {
        switch ($action) {
            case 'destroy':
                return [];
            case 'update':
                return array_merge(
                    static::$rules,
                    [
                        'area' => ['nullable', new Polygon],
                        'parent_id' => [
                            'nullable',
                            'different:id',
                            'exists:communities,id',
                            Rule::exists('communities', 'id')->where(function ($q) {
                                return $q->where('type', 'borough');
                            }),
                        ],
                    ]
                );
                break;
            default:
                return array_merge(
                    static::$rules,
                    [
                        'area' => ['nullable', new Polygon],
                        'parent_id' => [
                            'nullable',
                            'exists:communities,id',
                            Rule::exists('communities', 'id')->where(function ($q) {
                                return $q->where('type', 'borough');
                            }),
                        ],
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

            'users_count' => function ($query = null) {
                $usersCountSql = <<<SQL
(SELECT count(id) FROM community_user WHERE community_user.community_id = communities.id)
SQL
                ;

                if (!$query) {
                    return $usersCountSql;
                }

                return $query->selectRaw("$usersCountSql AS users_count");
            },

            'parent_name' => function ($query = null) {
                if (!$query) {
                    return 'parent.name';
                }

                $query->selectRaw('parent.name AS parent_name');

                $query = static::addJoin(
                    $query,
                    'communities AS parent',
                    'parent.id',
                    '=',
                    'communities.parent_id'
                );

                return $query;
            },
        ];
    }

    protected $fillable = [
        'area',
        'chat_group_url',
        'description',
        'long_description',
        'name',
        'type',
        'parent_id',
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

    public $items = ['parent'];

    public $collections = ['children', 'users', 'pricings', 'loanables'];

    public $computed =  ['area_google', 'center_google', 'users_count'];

    public function parent() {
        return $this->belongsTo(Community::class, 'parent_id');
    }

    public function children() {
        return $this->hasMany(Community::class, 'parent_id');
    }

    public function loanables() {
        return $this->hasMany(Loanable::class);
    }

    public function pricings() {
        return $this->hasMany(Pricing::class)->orderBy('object_type', 'desc');
    }

    public function users() {
        return $this->belongsToMany(User::class)
            ->using(Pivots\CommunityUser::class)
            ->withTimestamps()
            ->withPivot(['id', 'approved_at', 'created_at', 'role', 'suspended_at', 'updated_at']);

        $user = Auth::user();
        if ($user && $user->isAdmin()) {
            return $relation;
        }

        return $relation->whereSuspendedAt(null);
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

    public function getUsersCountAttribute() {
        if (isset($this->attributes['users_count'])) {
            return $this->attributes['users_count'];
        }

        return $this->users->count();
    }

    public function scopeAccessibleBy(Builder $query, $user) {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            return $q->whereHas('users', function ($q2) use ($user) {
                return $q2->where('users.id', $user->id);
            })->orWhere('communities.type', '!=', 'private');
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
            case 'loan':
                return $query->whereHas('users', function ($q) use ($user) {
                    return $q
                        ->where('community_user.user_id', $user->id)
                        ->whereNotNull('community_user.approved_at')
                        ->whereNull('community_user.suspended_at');
                });
            case 'edit':
                return $query
                    ->whereHas('users', function ($q) use ($user) {
                        return $q->where('community_user.user_id', $user->id)
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
