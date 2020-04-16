<?php

namespace App\Models;

use App\Models\Loanable;
use Illuminate\Database\Eloquent\Builder;

class Car extends Loanable
{
    public static $rules = [
        'brand' => [ 'required' ],
        'comments' => [ 'present' ],
        'engine' => [
            'required',
            'in:fuel,diesel,electric,hybrid',
        ],
        'instructions' => [ 'present' ],
        'insurer' => [ 'required' ],
        'is_value_over_fifty_thousand' => [ 'boolean' ],
        'location_description' => [ 'present' ],
        'model' => [ 'required' ],
        'name' => [ 'required' ],
        'ownership' => [
            'required',
            'in:self,rental',
        ],
        'position' => [ 'required' ],
        'papers_location' => [
            'required' ,
            'in:in_the_car,to_request_with_car'
        ],
        'plate_number' => [ 'required' ],
        'transmission_mode' => [
            'required' ,
            'in:manual,automatic',
        ],
        'type' => [
            'required',
            'in:car'
        ],
        'year_of_circulation' => [
            'required',
            'digits:4',
        ],
    ];

    public static function getColumnsDefinition() {
        return [
            '*' => function ($query = null) {
                if (!$query) {
                    return 'cars.*';
                }

                return $query->selectRaw('cars.*');
            },
            'type' => function ($query = null) {
                if (!$query) {
                    return "'car' AS type";
                }

                return $query->selectRaw("'car' AS type");
            }
        ];
    }

    protected $table = 'cars';

    protected $fillable = [
        'availability_json',
        'availability_mode',
        'brand',
        'comments',
        'engine',
        'has_accident_report',
        'has_informed_insurer',
        'instructions',
        'insurer',
        'is_value_over_fifty_thousand',
        'location_description',
        'model',
        'name',
        'ownership',
        'papers_location',
        'plate_number',
        'position',
        'transmission_mode',
        'year_of_circulation',
    ];

    public $items = ['owner', 'community'];

    public $morphOnes = [
        'image' => 'imageable',
    ];

    public function image() {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function loans() {
        return $this->hasMany(Loan::class, 'loanable_id');
    }

    public function padlock() {
        return $this->morphOne(Padlock::class, 'loanable')->where(\DB::raw('1 = 0'));
    }

    public function scopeAccessibleBy(Builder $query, $user) {
        if ($user->isAdmin()) {
            return $query;
        }

        if (!$user->borrower || !$user->borrower->validated) {
            return $query->whereHas('owner', function ($q) use ($user) {
                return $q->where('id', $user->owner->id);
            });
        }

        // A user has access to...
        $communityIds = $user->communities->pluck('id');
        return $query->where(function ($q) use ($communityIds) {
            return $q
                // ...loanables belonging to its accessible communities...
                ->whereHas('community', function ($q2) use ($communityIds) {
                    return $q2->whereIn(
                        'communities.id',
                        $communityIds
                    );
                })
                // ...or belonging to owners of his accessible communities
                // (communities through user through owner)
                ->orWhereHas('owner', function ($q3) use ($communityIds) {
                    return $q3->whereHas('user', function ($q4) use ($communityIds) {
                        return $q4->whereHas('communities', function ($q5) use ($communityIds) {
                            return $q5->whereIn('community_user.community_id', $communityIds);
                        });
                    });
                });
        });
    }
}
