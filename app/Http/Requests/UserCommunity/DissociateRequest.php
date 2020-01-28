<?php

namespace App\Http\Requests\UserCommunity;

use App\Http\Requests\BaseRequest;

class DissociateRequest extends BaseRequest
{
    public function authorize() {
        return true;
    }

    public function rules() {
        $rules = [
        ];

        return $rules;
    }

    public function messages() {
        return [
        ];
    }
}
