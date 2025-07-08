<?php

namespace App\Http\Requests\User;

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
        $rules = [
            "email" => ["email", "unique:users,email"],
        ];

        if ($userId = $this->get("id")) {
            $rules["email"] = ["email", "unique:users,email,$userId"];
        }

        if ($this->user()->isCommunityAdmin()) {
            $accessibleCommunityIds = implode(
                ",",
                Community::accessibleBy($this->user())
                    ->pluck("id")
                    ->toArray()
            );
            $rules["communities.*.id"] = [
                "numeric",
                "in:$accessibleCommunityIds",
            ];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            "email.unique" => "Le courriel est déjà pris.",
            "communities.*.id.in" => "Vous n'avez pas accès à cette communauté.",
        ];
    }
}
