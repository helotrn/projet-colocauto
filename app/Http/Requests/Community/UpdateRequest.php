<?php

namespace App\Http\Requests\Community;

use App\Http\Requests\BaseRequest;
use App\Models\Community;
use App\Models\Pricing;
use App\Rules\CommunityPricingRule;

class UpdateRequest extends BaseRequest
{
    public function authorize()
    {
        $id = $this->route("id");
        return $this->user()->isAdmin() ||
            $this->user()->isAdminOfCommunity($id) ||
            $this->user()->isResponsibleOfCommunity($id) ||
            ($this->user()->isCommunityAdmin() && Community::accessibleBy($this->user())->find($id));
    }

    public function rules()
    {
        $id = $this->get("id");
        $parentIds = Community::parentOf($id)
            ->pluck("id")
            ->toArray();
        $childIds = Community::childOf($id)
            ->pluck("id")
            ->toArray();

        $ids = array_merge($parentIds, $childIds, [$this->get("id")]);

        $currentCommunity = Community::find($id);
        if ($currentCommunity && $currentCommunity->parent) {
            $ids = array_diff($ids, [$currentCommunity->parent->id]);
        }

        $rules = [
            "pricings" => [new CommunityPricingRule()],
            "parent_id" => ["not_in:" . implode(",", $ids)],
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
            "parent_id.not_in" => "L'association à ce parent crée une boucle.",
        ];
    }
}
