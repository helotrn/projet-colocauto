<?php

namespace App\Http\Requests\Invitation;

use App\Http\Requests\BaseRequest;
use App\Models\Community;

class CreateRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
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
            "email" => "string|required",
             // force false or null for non admins
            "for_community_admin" => "nullable|boolean".($this->user()->isAdmin() ? "" : "|in:false"),
            "community_id" => [
                "nullable",
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
