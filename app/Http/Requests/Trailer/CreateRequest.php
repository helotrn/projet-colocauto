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
            'location_description' => 'required|string',
            'comments' => 'required|string',
            'instructions' => 'required|string',
            'maximum_charge' => 'required',
            'type' => 'required',
        ];

        return $rules;
    }

    public function messages() {
        return [
            'name.required' => 'Le nom est requis.'
        ];
    }
}
