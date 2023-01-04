<?php

namespace App\Http\Requests\Expense;

use App\Http\Requests\BaseRequest;

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
