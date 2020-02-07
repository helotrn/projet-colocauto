<?php

namespace App\Models;

use App\Models\Owner;
use App\Utils\PointCast;
use Illuminate\Database\Eloquent\Builder;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Vkovic\LaravelCustomCasts\HasCustomCasts;

class Loanable extends BaseModel
{
    use HasCustomCasts, PostgisTrait;

    public static $filterTypes = [
        'id' => 'number',
        'name' => 'text',
        'type' => ['bike', 'car', 'trailer'],
    ];

    protected $table = 'loanables';

    protected $postgisFields = [
        'position',
    ];

    protected $postgisTypes = [
        'position' => [
            'geomtype' => 'geography',
        ],
    ];

    protected $casts = [
        'position' => PointCast::class,
    ];

    public $belongsTo = ['owner'];

    public function owner() {
        return $this->belongsTo(Owner::class);
    }
}
