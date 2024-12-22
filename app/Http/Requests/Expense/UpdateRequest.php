<?php

namespace App\Http\Requests\Expense;

use App\Http\Requests\BaseRequest;
use App\Models\Loanable;
use App\Models\User;
use App\Models\Loan;
use App\Models\Expense;

class UpdateRequest extends BaseRequest
{
    public function authorize()
    {
        return $this->user()->isAdmin() ||
            Expense::accessibleBy($this->user())->find($this->route('id'));
    }

    public function rules()
    {
        $rules = [
            "amount" => [
                "numeric",
                "required",
                "gte:0"
            ],
            "loanable_id" => [
                "numeric",
                "required",
            ],
            "user_id" => [
                "numeric",
                "required",
            ],
            "loan_id" => [
                "numeric",
            ],
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

            $accessibleLoanIds = implode(
                ",",
                Loan::accessibleBy($user)
                    ->pluck("id")
                    ->toArray()
            );
            $rules["loan_id"][] = "in:$accessibleLoanIds";
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
