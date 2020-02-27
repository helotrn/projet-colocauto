<?php

namespace App\Models;

use App\Models\Community;
use App\Models\Loan;
use App\Models\Owner;
use App\Utils\PointCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Vkovic\LaravelCustomCasts\HasCustomCasts;

class Loanable extends BaseModel
{
    use HasCustomCasts, PostgisTrait, SoftDeletes;

    public static $filterTypes = [
        'id' => 'number',
        'name' => 'text',
        'type' => ['bike', 'car', 'trailer'],
    ];

    public static $rules = [
        'name' => [ 'required' ],
        'position' => [ 'required' ],
        'type' => [
            'required',
            'in:car,bike,trailer',
        ],
        'location_description' => [ 'present' ],
        'instructions' => [ 'present' ],
        'comments' => [ 'present' ],
        'availability_ics' => [ 'present' ],
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

    public $items = ['owner', 'community'];

    public function community() {
        return $this->belongsTo(Community::class);
    }

    public function owner() {
        return $this->belongsTo(Owner::class);
    }

    public $collections = ['loans'];

    public function loans() {
        return $this->morphMany(Loan::class, 'loanable');
    }
}
