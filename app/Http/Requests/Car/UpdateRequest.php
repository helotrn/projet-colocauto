<?php

namespace App\Http\Requests\Car;

use App\Http\Requests\BaseRequest;

class UpdateRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [];

        return $rules;
    }

    public function messages()
    {
        return [];
    }
}
