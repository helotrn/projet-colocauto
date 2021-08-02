<?php

namespace App\Http\Requests\Loan;

use App\Http\Requests\BaseRequest;
use App\Models\Community;
use App\Models\Loanable;

class CreateRequest extends BaseRequest
{
    public function rules()
    {
        $user = $this->user();
        $accessibleCommunityIds = implode(
            ",",
            Community::accessibleBy($user)
                ->for("loan", $user)
                ->pluck("id")
                ->toArray()
        );
        $accessibleLoanableIds = implode(
            ",",
            Loanable::accessibleBy($user)
                ->pluck("id")
                ->toArray()
        );

        return [
            "loanable_id" => [
                "numeric",
                "required",
                "in:$accessibleLoanableIds",
            ],
            "community_id" => [
                "numeric",
                "filled",
                "in:$accessibleCommunityIds",
            ],
        ];
    }

    public function messages()
    {
        return [
            "community_id.in" => "Vous n'avez pas accès à cette communauté.",
        ];
    }
}
