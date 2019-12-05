<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\PaymentMethod;
use App\Repositories\PaymentMethodRepository;

class PaymentMethodController extends RestController
{
    public function __construct(PaymentMethodRepository $repository, PaymentMethod $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
