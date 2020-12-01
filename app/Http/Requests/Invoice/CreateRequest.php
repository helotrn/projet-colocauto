<?php

namespace App\Http\Requests\Invoice;

use App\Http\Requests\BaseRequest;

class CreateRequest extends BaseRequest
{
    public function authorize() {
        return $this->user()->isAdmin();
    }

    public function rules() {
        return [
            'period' => 'required',
            'bill_items' => 'array|min:1',
            'apply_to_balance' => 'required|boolean',
        ];
    }

    public function attributes() {
        return [
            'period' => 'Titre',
        ];
    }
}
