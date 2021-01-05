<?php

namespace App\Repositories;

use App\Models\Payment;
use Molotov\RestRepository;

class PaymentRepository extends RestRepository
{
    public function __construct(Payment $model) {
        $this->model = $model;
    }
}
