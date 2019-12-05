<?php

namespace App\Repositories;

use App\Http\Requests\BaseRequest as Request;
use App\Models\PaymentMethod;

class PaymentMethodRepository extends RestRepository
{
    public function __construct(PaymentMethod $model) {
        $this->model = $model;
    }
}
