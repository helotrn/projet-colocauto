<?php

namespace App\Repositories;

use App\Models\Bill;
use Molotov\Repositories\RestRepository;

class BillRepository extends RestRepository
{
    public function __construct(Bill $model) {
        $this->model = $model;
    }
}
