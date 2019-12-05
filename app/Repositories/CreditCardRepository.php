<?php

namespace App\Repositories;

use App\Http\Requests\BaseRequest as Request;
use App\Models\CreditCard;

class CreditCardRepository extends RestRepository
{
    public function __construct(CreditCard $model) {
        $this->model = $model;
    }
}
