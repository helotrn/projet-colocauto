<?php

namespace App\Http\Requests\UserCommunity;

use App\Http\Requests\BaseRequest;

class AssociateRequest extends BaseRequest
{
    public function authorize() {
        $communityId = $this->route('community_id');
        return $this->user()->isAdmin() || $this->user()->isAdminOfCommunity($communityId);
    }

    public function rules() {
        $rules = [
        ];

        return $rules;
    }

    public function messages() {
        return [
        ];
    }
}
