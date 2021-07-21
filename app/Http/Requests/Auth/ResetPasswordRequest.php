<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

class ResetPasswordRequest extends BaseRequest
{
    public function rules()
    {
        return [
            "password" => "string|required|min:8",
            "token" => "string|required",
            "email" => "email|required|exists:password_resets,email",
        ];
    }
}
