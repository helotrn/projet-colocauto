<?php

namespace App\Http\Requests\Loanable;

use App\Http\Requests\BaseRequest;
use App\Models\Loanable;

class RemoveCoownerRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }
        $loanable = Loanable::find($this->route("loanable_id"));

        return $user->is($loanable->owner->user) ||
            $user->isAdmin() ||
            ($user->isCommunityAdmin() && Loanable::accessibleBy($user)->find($loanable->id)) ||
            ($loanable->isCoowner($user) && $this->get("user_id") == $user->id);
    }

    public function rules(): array
    {
        $loanable = Loanable::find($this->route("loanable_id"));

        $currentCoowners = $loanable->coowners->pluck("user_id")->join(",");

        return [
            "user_id" => ["required", "in:$currentCoowners"],
        ];
    }
}
