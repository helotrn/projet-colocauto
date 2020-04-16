<?php

namespace App\Http\Requests\Loanable;

use App\Http\Requests\BaseRequest;
use App\Models\Loanable;

class RestoreRequest extends BaseRequest
{
    public function authorize() {
        $user = $this->user();
        return $user->isAdmin()
            || Loanable::where('owner_id', $user->owner->id)
                ->withTrashed()
                ->where('id', $this->route('id'))
                ->exists();
    }
}
