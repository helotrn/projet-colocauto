<?php

namespace App\Http\Requests\Community;

use App\Http\Requests\BaseRequest;

class UpdateRequest extends BaseRequest
{
    public function authorize() {
        $id = $this->route('id');
        return $this->user()->isAdmin() || $this->user()->isAdminOfCommunity($id);
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
