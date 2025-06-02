<?php

namespace App\Http\Requests\Expense;

use App\Http\Requests\BaseRequest;
use App\Models\Loanable;
use App\Models\User;
use App\Models\Loan;

class CreateRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
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
                "nullable",
            ],
        ];

        if( !$this->user()->isAdmin() ) {
            $user = $this->user();
            $accessibleUserIds = implode(
                ",",
                User::accessibleBy($user)
                    ->pluck("id")
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
