<?php

namespace App\Repositories;

use App\Models\PaymentMethod;
use Molotov\Repositories\RestRepository;

class PaymentMethodRepository extends RestRepository
{
    public function __construct(PaymentMethod $model) {
        $this->model = $model;
    }
}
