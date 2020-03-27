<?php

namespace App\Http\Requests\Trailer;

use App\Http\Requests\BaseRequest;

class CreateRequest extends BaseRequest
{
    public function authorize() {
        return true;
    }

    public function rules() {
        $rules = [
            'name' => 'required',
            'position' => 'required',
            'location_description' => 'present',
            'comments' => 'present',
            'instructions' => 'present',
            'maximum_charge' => 'required',
        ];

        return $rules;
    }

    public function messages() {
        return [
            'name.required' => 'Le nom est requis.'
        ];
    }
}
