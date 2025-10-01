<?php

namespace App\Http\Requests\Community;

use App\Http\Requests\BaseRequest;
use App\Models\User;

class UpdateUserRequest extends BaseRequest
{
    public function authorize()
    {
        return $this->user()->isAdmin() ||
            $this->user()->isAdminOfCommunity($this->route("community_id"));
    }
}
