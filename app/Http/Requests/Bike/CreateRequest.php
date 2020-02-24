<?php

namespace App\Http\Requests\Bike;

use App\Http\Requests\BaseRequest;

class CreateRequest extends BaseRequest
{
    public function authorize() {
        return true;
    }

    public function rules() {
        $rules = [
            'bike_type' => 'required',
            'comments' => 'required',
            'instructions' => 'required',
            'location_description' => 'required',
            'model' => 'required',
            'name' => 'required',
            'position' => 'required',
            'size' => 'required',
        ];

        return $rules;
    }

    public function messages() {
        return [
            'name.required' => 'Le nom est requis.'
        ];
    }
}
