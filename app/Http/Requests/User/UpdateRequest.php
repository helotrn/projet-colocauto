<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class UpdateRequest extends BaseRequest
{
    public function authorize() {
        return true;
    }

    public function rules() {
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];

        if ($user = Auth::user()) {
            $rules['email'] = "required|email|unique:users,email,$user->id";
        }

        return $rules;
    }

    public function messages() {
        return [];
    }
}
