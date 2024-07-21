<?php

namespace App\Http\Requests\Car;

use App\Http\Requests\BaseRequest;
use App\Models\Community;

class CreateRequest extends BaseRequest
{
    public function authorize()
    {
        // only authorize admins to create cars
        $user = $this->user();

        if (!$user) {
            return false;
        }

        if ($user->isAdmin() || $user->isCommunityAdmin()) {
            return true;
        }

        return false;
    }

    public function rules()
    {
        $rules = [
            "brand" => "required",
            "comments" => "present",
            "engine" => "required",
            "instructions" => "present",
            "insurer" => "required",
            "is_value_over_fifty_thousand" => "boolean",
            "location_description" => "present",
            "model" => "required",
            "name" => "required",
            "papers_location" => "required",
            "plate_number" => "present",
            "transmission_mode" => "required",
            "year_of_circulation" => "nullable|digits:4",
        ];

        if ($this->user()->isCommunityAdmin()) {
            $accessibleCommunityIds = implode(
                ",",
                Community::accessibleBy($this->user())
                    ->pluck("id")
                    ->toArray()
            );
            $rules["community"] = ["required", "filled"];
            $rules["community.id"] = [
                "required",
                "numeric",
                "filled",
                "in:$accessibleCommunityIds",
            ];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            "name.required" => "Le nom est requis.",
            "community.id.in" => "Vous n'avez pas accès à cette communauté.",
        ];
    }
}
