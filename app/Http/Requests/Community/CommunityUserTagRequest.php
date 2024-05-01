<?php

namespace App\Http\Requests\Community;

use App\Http\Requests\BaseRequest;

class CommunityUserTagRequest extends BaseRequest
{
    public function authorize()
    {
        $user = $this->user();

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCommunityAdmin()) {
            if (Community::accessibleBy($user)->find($this->route('community_id'))) {
                return true;
            }
        }

        if ($user->isAdminOfCommunity($this->route("community_id"))) {
            return true;
        }

        return false;
    }
}
