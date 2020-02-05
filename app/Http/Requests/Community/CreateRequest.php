<?php

namespace App\Http\Requests\Community;

use App\Http\Requests\BaseRequest;

class CreateRequest extends BaseRequest
{
    public function authorize() {
        return $this->user()->isAdmin();
    }

    public function rules() {
        $rules = [
            'name' => 'string',
        ];

        return $rules;
    }

    public function messages() {
        return [
            'name.required' => 'Le nom est requis.'
        ];
    }
}
