<?php

namespace App\Http\Requests\Loan;

use App\Http\Requests\BaseRequest;
use App\Models\Community;

class CreateRequest extends BaseRequest
{
    public function rules() {
        $user = $this->user();
        $accessibleCommunityIds = implode(
            ',',
            Community::accessibleBy($user)
                ->for('loan', $user)
                ->pluck('id')
                ->toArray()
        );

        return [
            'community_id' => [
                'required',
                "in:$accessibleCommunityIds",
            ],
        ];
    }

    public function messages() {
        return [
            'community_id.in' => "Vous n'avez pas accès à cette communauté.",
        ];
    }
}
