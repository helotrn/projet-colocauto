<?php

namespace App\Http\Requests\Invitation;

use App\Http\Requests\BaseRequest;
use App\Models\Community;

class CreateRequest extends BaseRequest
{
    public function authorize()
    {
        return $this->user()->isAdmin() || $this->user()->isCommunityAdmin();
    }

    public function rules()
    {

        $accessibleCommunityIds = implode(
            ",",
            Community::accessibleBy($this->user())
                ->pluck("id")
                ->toArray()
        );

        return [
            "email" => "string",
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
            "email.required" => "L'adresse email est requise.",
            "community_id.in" => "Vous n'avez pas accès à cette communauté.",
        ];
    }
}
