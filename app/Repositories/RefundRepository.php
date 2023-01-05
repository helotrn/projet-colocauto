<?php

namespace App\Repositories;

use App\Models\Refund;

class RefundRepository extends RestRepository
{
    public function __construct(Refund $model)
    {
        $this->model = $model;
    }
}

