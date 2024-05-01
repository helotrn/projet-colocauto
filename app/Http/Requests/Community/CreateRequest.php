<?php

namespace App\Http\Requests\Community;

use App\Http\Requests\BaseRequest;
use App\Models\Pricing;
use App\Rules\CommunityPricingRule;

class CreateRequest extends BaseRequest
{
    public function authorize()
    {
        return $this->user()->isAdmin()  || $this->user()->isCommunityAdmin();
    }

    public function rules()
    {
        $rules = [
            "name" => "string",
        ];

        $pricingRules = Pricing::getRules("update", $this->user());
        $rules = array_merge(
            $rules,
            static::rebaseRules("pricings.*", $pricingRules)
        );

        return $rules;
    }

    public function messages()
    {
        return [
            "name.required" => "Le nom est requis.",
        ];
    }
}
