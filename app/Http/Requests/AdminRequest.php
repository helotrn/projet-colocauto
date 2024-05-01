<?php

namespace App\Http\Requests;
use App\Models\User;

class AdminRequest extends BaseRequest
{
    public function authorize()
    {
        if (!$this->user()) {
            return false;
        }

        if( $this->user()->isAdmin() ){
            return true;
        }

        // community admin can only access users of its communities
        if( $this->user()->isCommunityAdmin() ){
            if (User::accessibleBy($user)->find($this->get('id'))) {
                return true;
            }
        }

        return false;
    }
}
