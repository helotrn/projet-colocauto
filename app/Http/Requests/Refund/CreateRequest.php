<?php

namespace App\Http\Requests\Refund;

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
            "user_id" => [
                "numeric",
                "required",
            ],
            "credited_user_id" => [
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
            "amount" => "montant"
        ];
    }

    public function messages()
    {
        return [
            "credited_user_id.required" => "Vous devez indiquer à qui vous avez remboursé une somme.",
        ];
    }
}
