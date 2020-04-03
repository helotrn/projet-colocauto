<?php

namespace App\Http\Requests\Loanable;

use App\Http\Requests\BaseRequest;
use App\Models\Community;
use App\Models\Loanable;

class TestRequest extends BaseRequest
{
    public function authorize() {
        return true;
    }

    public function rules() {
        $rules = [
            'departure_at' => ['date', 'required'],
            'duration_in_minutes' => ['integer', 'required', 'min:1'],
            'estimated_distance' => ['integer', 'required', 'min:1'],
            'loanable_id' => ['integer', 'required'],
            'community_id' => ['integer', 'required']
        ];

        $user = $this->user();

        $loanableIds = Loanable::accessibleBy($user)->pluck('id')->join(',');
        $rules['loanable_id'][] = "in:$loanableIds";

        $communityIds = Community::accessibleBy($user)->pluck('id')->join(',');
        $rules['community_id'][] = "in:$communityIds";

        return $rules;
    }
}
