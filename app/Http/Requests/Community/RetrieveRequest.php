<?php

namespace App\Http\Requests\Community;

use App\Http\Requests\BaseRequest;

class RetrieveRequest extends BaseRequest
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
