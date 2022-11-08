<?php

namespace App\Http\Requests\Invitation;

use App\Http\Requests\BaseRequest;

class CreateRequest extends BaseRequest
{
    public function authorize()
    {
        return $this->user()->isAdmin();
    }

    public function rules()
    {
        $rules = [
            "email" => "string",
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            "email.required" => "L'adresse email est requise.",
        ];
    }
}
