<?php

namespace App\Http\Requests\Invitation;

use App\Http\Requests\BaseRequest;
use App\Models\Community;

class AcceptRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            "token" => "string|required",
        ];
    }
}
