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

        // only super admin can invite community admins without communities
        $required = $this->user()->isAdmin() ? "required_unless:for_community_admin,true" : "required";

        return [
            "email" => "string|required",
            "community_id" => [
                $required,
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
            "community_id.required_if" => "Vous devez indiquer une communauté.",
        ];
    }
}
