<?php

namespace App\Repositories;

use App\Models\Pricing;
use Molotov\RestRepository;

class PricingRepository extends RestRepository
{
    public function __construct(Pricing $model) {
        $this->model = $model;
    }
}
