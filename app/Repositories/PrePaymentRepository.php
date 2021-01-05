<?php

namespace App\Repositories;

use App\Models\PrePayment;
use Molotov\RestRepository;

class PrePaymentRepository extends RestRepository
{
    public function __construct(PrePayment $model) {
        $this->model = $model;
    }
}
