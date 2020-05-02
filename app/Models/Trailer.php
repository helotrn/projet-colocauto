<?php

namespace App\Models;

use App\Models\Loanable;
use Illuminate\Database\Eloquent\Builder;

class Trailer extends Loanable
{
    public static function getColumnsDefinition() {
        return [
            '*' => function ($query = null) {
                if (!$query) {
                    return 'trailers.*';
                }

                return $query->selectRaw('trailers.*');
            },
            'type' => function ($query = null) {
                if (!$query) {
                    return "'trailer' AS type";
                }

                return $query->selectRaw("'trailer' AS type");
            }
        ];
    }

    protected $fillable = [
        'availability_json',
        'availability_mode',
        'comments',
        'instructions',
        'location_description',
        'maximum_charge',
        'name',
        'position',
    ];

    public $readOnly = false;

    public $items = ['community','owner', 'padlock'];

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
        return $this->hasOne(Padlock::class, 'loanable_id');
    }

    public function scopeAccessibleBy(Builder $query, $user) {
        if ($user->isAdmin()) {
            return $query;
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
