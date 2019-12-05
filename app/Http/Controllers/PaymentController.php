<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Payment;
use App\Repositories\PaymentRepository;

class PaymentController extends RestController
{
    public function __construct(PaymentRepository $repository, Payment $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
