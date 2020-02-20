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
            'availability_ics' => 'required',
            'brand' => 'required',
            'comments' => 'required',
            'engine' => 'required',
            'has_accident_report' => 'accepted',
            'has_informed_insurer' => 'accepted',
            'instructions' => 'required',
            'insurer' => 'required',
            'is_value_over_fifty_thousand' => 'boolean',
            'location_description' => 'required',
            'model' => 'required',
            'name' => 'required',
            'ownership' => 'required',
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
