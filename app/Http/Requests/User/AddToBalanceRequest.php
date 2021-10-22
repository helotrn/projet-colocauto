<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class AddToBalanceRequest extends BaseRequest
{
    public function authorize()
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $routeUserId = $this->route("user_id") ?: $this->get("user_id");

        return $user->id === $routeUserId;
    }

    public function rules()
    {
        $user = $this->user();
        $transactionId = $user->transaction_id ?: 0;

        $rules = [
            "amount" => ["numeric", "required"],
            "transaction_id" => ["required", "integer", "gt:$transactionId"],
        ];

        return $rules;
    }
}
