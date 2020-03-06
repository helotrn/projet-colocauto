<?php

namespace App\Http\Requests\Community;

use App\Http\Requests\BaseRequest;

class DestroyRequest extends BaseRequest
{
    public function authorize() {
        return $this->user()->isAdmin();
    }

    public function rules() {
        $rules = [];

        return $rules;
    }

    public function messages() {
        return [
        ];
    }
}
