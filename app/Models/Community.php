<?php

namespace App\Models;

use App\Models\Pricing;
use App\Utils\PointCast;
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
    ];

    protected $fillable = [
        'name',
        'description',
        'area',
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
    ];

    public static $transformer = CommunityTransformer::class;

    public static function getColumnsDefinition() {
        return [
            '*' => function ($query = null) {
                if (!$query) {
                    return 'ST_Centroid(communities.area::geometry)';
                }

                return $query->selectRaw('communities.*');
            },
            'center' => function ($query = null) {
                if (!$query) {
                    return 'communities.area';
                }

                return $query->selectRaw('ST_Centroid(communities.area::geometry) AS center');
            }
        ];
    }

    public $collections = ['users', 'pricings'];

    public function users() {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivot(['role', 'created_at', 'updated_at']);
    }

    public function pricings() {
        return $this->hasMany(Pricing::class);
    }
}
