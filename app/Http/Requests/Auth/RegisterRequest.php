<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

class RegisterRequest extends BaseRequest
{
    public function authorize() {
        return true;
    }

    public function rules() {
        return [
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ];
    }

    public function messages() {
        return [];
    }
}
