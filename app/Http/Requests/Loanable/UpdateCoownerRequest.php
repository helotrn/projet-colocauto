<?php

namespace App\Http\Requests\Loanable;

use App\Http\Requests\BaseRequest;
use App\Models\Loanable;
use App\Models\Coowner;

class UpdateCoownerRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }
        $loanable = Loanable::find($this->route("loanable_id"));
        $coowner = Coowner::find($this->route("coowner_id"));
        if( !$loanable->coowners->contains($coowner) ) {
            return false;
        }

        return $user->is($loanable->owner->user) ||
            $user->isAdmin() ||
            ($user->isCommunityAdmin() && Loanable::accessibleBy($user)->find($loanable->id) ||
            $loanable->isCoowner($user));
    }

    public function rules(): array
    {
        return [
            "title" => "string",
            "receive_notifications" => "boolean",
        ];
    }
}
