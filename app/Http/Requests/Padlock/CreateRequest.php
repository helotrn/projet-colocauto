<?php

namespace App\Http\Requests\Padlock;

use App\Http\Requests\BaseRequest;

class CreateRequest extends BaseRequest
{
    public function authorize() {
        $user = $this->user();
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    public function rules() {
        return [
            'external_id' => 'required',
            'mac_address' => 'required',
            'name' => 'required',
        ];
    }
}
