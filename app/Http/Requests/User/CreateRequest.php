<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class CreateRequest extends BaseRequest
{
    public function authorize()
    {
        return $this->user()->isAdmin() || $this->user()->isCommunityAdmin();
    }

    public function rules()
    {
        $rules = [
            "email" => ["email", "unique:users,email"],
        ];

        if ($userId = $this->get("id")) {
            $rules["email"] = ["email", "unique:users,email,$userId"];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            "email.unique" => "Le courriel est déjà pris.",
        ];
    }
}
