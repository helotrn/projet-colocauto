<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class DestroyRequest extends BaseRequest
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
