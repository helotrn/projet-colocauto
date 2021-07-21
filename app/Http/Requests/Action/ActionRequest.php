<?php

namespace App\Http\Requests\Action;

use App\Http\Requests\BaseRequest;

class ActionRequest extends BaseRequest
{
    public function rules()
    {
        return [
            "type" => [
                "required",
                "in:pre_payment,payment,takeover,handover,incident,intention,extension",
            ],
        ];
    }
}
