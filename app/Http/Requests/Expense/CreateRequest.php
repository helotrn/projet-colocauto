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

        return [
            // TODO manage access rules
        ];
    }

    public function messages()
    {
        return [
            // TODO manage error messages
        ];
    }
}
