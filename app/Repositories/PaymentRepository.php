<?php

namespace App\Repositories;

use App\Models\Payment;

class PaymentRepository extends RestRepository
{
    public function __construct(Payment $model) {
        $this->model = $model;
    }
}
