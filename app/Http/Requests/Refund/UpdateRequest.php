<?php

namespace App\Http\Requests\Refund;

use App\Http\Requests\BaseRequest;
use App\Models\User;

class UpdateRequest extends BaseRequest
{
    public function authorize()
    {
        return $this->user()->isAdmin() ||
            (User::accessibleBy($this->user())->find($this->route("user_id"))
            && User::accessibleBy($this->user())->find($this->route("credited_user_id")));
    }

    public function rules()
    {
        // TODO change rules depending on the user
        $rules = [
            "amount" => [
                "numeric",
                "required",
                "gte:0"
            ],
            "user_id" => [
                "numeric",
                "required",
            ],
            "credited_user_id" => [
                "numeric",
                "required",
                "different:user_id"
            ]
        ];

        if( !$this->user()->isAdmin() && !$this->user()->isCommunityAdmin()) {
            $user = $this->user();
            $accessibleUserIds = implode(
                ",",
                $user->getSameCommunityUserIds()
                    ->toArray()
            );
            $rules["user_id"][] = "in:$accessibleUserIds";
            $rules["credited_user_id"][] = "in:$accessibleUserIds";
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            "user_id" => "payé par",
            "amount" => "montant",
            "credited_user_id" => "payé à",
        ];
    }

    public function messages()
    {
        return [
            "credited_user_id.required" => "Vous devez indiquer à qui vous avez remboursé une somme.",
            "credited_user_id.different" => "La personne qui paye et celle qui est remboursée doivent être différentes",
        ];
    }
}
