<?php

namespace App\Http\Requests\Community;

use App\Http\Requests\BaseRequest;
use App\Models\Pricing;

class UpdateRequest extends BaseRequest
{
    public function authorize() {
        $id = $this->route('id');
        return $this->user()->isAdmin() || $this->user()->isAdminOfCommunity($id);
    }

    public function rules() {
        $pricingRules = Pricing::getRules('update', $this->user());
        $rules = static::rebaseRules('pricings.*', $pricingRules);

        return $rules;
    }

    public function messages() {
        return [];
    }
}
