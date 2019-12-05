<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Pricing;
use App\Repositories\PricingRepository;

class PricingController extends RestController
{
    public function __construct(PricingRepository $repository, Pricing $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
