<?php

namespace App\Models;

use App\Models\Owner;
use App\Utils\PointCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Vkovic\LaravelCustomCasts\HasCustomCasts;

class Loanable extends BaseModel
{
    use HasCustomCasts, PostgisTrait;

    protected $table = 'loanables';

    public $belongsTo = ['owner'];

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

    public function owner() {
        return $this->belongsTo(Owner::class);
    }
}
