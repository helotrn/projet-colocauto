<?php

namespace App\Repositories;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Pricing;

class PricingRepository extends RestRepository
{
    public function __construct(Pricing $model) {
        $this->model = $model;
    }
}
