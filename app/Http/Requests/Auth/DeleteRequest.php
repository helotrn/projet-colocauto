<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

class DeleteRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            "id" => "required",
        ];
    }
}
