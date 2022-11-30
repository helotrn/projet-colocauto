<?php

namespace App\Http\Requests\Loanable;

use App\Http\Requests\BaseRequest;
use App\Models\Loanable;
use App\Models\User;

class AddCoownerRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }
        $loanable = Loanable::find($this->route("loanable_id"));

        return $user->is($loanable->owner->user) || $user->isAdmin();
    }

    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();
        $usersSharingCommunity = User::whereHas(
            "approvedCommunities",
            function ($q) use ($user) {
                $q->withApprovedUser($user);
            }
        )
            ->pluck("id")
            ->join(",");

        return [
            "user_id" => ["required", "in:$usersSharingCommunity"],
        ];
    }
}
