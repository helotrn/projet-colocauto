<?php

namespace App\Http\Requests\Community;

use App\Http\Requests\BaseRequest;
use App\Models\Community;
use App\Models\Pricing;
use App\Rules\CommunityPricingRule;

class UpdateRequest extends BaseRequest
{
    public function authorize() {
        $id = $this->route('id');
        return $this->user()->isAdmin() || $this->user()->isAdminOfCommunity($id);
    }

    public function rules() {
        $parentIds = Community::parentOf($this->get('id'))->toArray();
        $childIds = Community::childOf($this->get('id'))->toArray();

        $ids = array_merge($parentIds, $childIds, [$this->get('id')]);

        $rules = [
            'pricings' => [
                new CommunityPricingRule,
            ],
            'parent_id' => [
                'not_in:' . implode(',', $ids),
            ],
        ];

        $pricingRules = Pricing::getRules('update', $this->user());
        $rules = array_merge($rules, static::rebaseRules('pricings.*', $pricingRules));

        return $rules;
    }

    public function messages() {
        return [
            'parent_id.not_in' => "L'association à ce parent crée une boucle.",
        ];
    }
}
