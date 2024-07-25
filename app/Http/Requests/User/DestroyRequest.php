<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use App\Models\User;

class DestroyRequest extends BaseRequest
{
    public function authorize()
    {
        $item = User::find($this->route("id"));
        return $this->user()->isAdmin() ||
            ($item && $item->communities->isEmpty() && $this->user()->isCommunityAdmin());
    }
}
