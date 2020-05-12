<?php

namespace App\Http\Requests\Car;

use App\Http\Requests\BaseRequest;

class CreateRequest extends BaseRequest
{
    public function authorize() {
        return true;
    }

    public function rules() {
        $rules = [
            'brand' => 'required',
            'comments' => 'present',
            'engine' => 'required',
            'instructions' => 'present',
            'insurer' => 'required',
            'is_value_over_fifty_thousand' => 'boolean',
            'location_description' => 'present',
            'model' => 'required',
            'name' => 'required',
            'papers_location' => 'required',
            'plate_number' => 'required',
            'position' => 'required',
            'transmission_mode' => 'required',
            'year_of_circulation' => 'required|digits:4',
        ];

        return $rules;
    }

    public function messages() {
        return [
            'name.required' => 'Le nom est requis.'
        ];
    }
}
