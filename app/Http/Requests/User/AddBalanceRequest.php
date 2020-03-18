<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class AddBalanceRequest extends BaseRequest
{
    public function rules() {
        $user = $this->user();

        $rules = [
            'amount' => [
                'numeric',
                'required',
            ],
            'transaction_id' => [
                'required',
                'integer',
                "gt:{$user->transaction_id}"
            ]
        ];

        return $rules;
    }
}
