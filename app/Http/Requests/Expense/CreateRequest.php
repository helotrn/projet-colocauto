<?php

namespace App\Http\Requests\Expense;

use App\Http\Requests\BaseRequest;
use App\Models\Loanable;

class CreateRequest extends BaseRequest
{
    public function authorize()
    {
        // TODO manage access rights
        return true;
    }

    public function rules()
    {
        // TODO change rules depending on the user
        $rules = [
            "amount" => [
                "numeric",
                "required",
                "gt:0"
            ],
            "loanable_id" => [
                "numeric",
                "required",
            ],
            "user_id" => [
                "numeric",
                "required",
            ]
        ];

        if( !$this->user()->isAdmin() ) {
            $user = $this->user();
            $accessibleUserIds = implode(
                ",",
                $user->getSameCommunityUserIds()
                    ->toArray()
            );
            $rules["user_id"][] = "in:$accessibleUserIds";

            $accessibleLoanableIds = implode(
                ",",
                Loanable::accessibleBy($user)
                    ->pluck("id")
                    ->toArray()
            );
            $rules["loanable_id"][] = "in:$accessibleLoanableIds";
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            "user_id" => "payé par",
            "loanable_id" => "véhicule",
            "amount" => "montant"
        ];
    }
}
