<?php

namespace App\Http\Requests\Action;

use App\Http\Requests\BaseRequest;
use App\Models\Loan;

class IncidentRequest extends BaseRequest
{
    public function authorize()
    {
        if ($this->user()->isAdmin()) {
            return true;
        }

        // community admin can only change loans that are parts of its communities
        if ($user->isCommunityAdmin()) {
            if (Loan::accessibleBy($user)->find($this->get("loan_id"))) {
                return true;
            }
        }

        return false;
    }
}
