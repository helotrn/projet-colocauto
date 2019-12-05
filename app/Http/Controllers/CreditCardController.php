<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\CreditCard;
use App\Repositories\CreditCardRepository;

class CreditCardController extends RestController
{
    public function __construct(CreditCardRepository $repository, CreditCard $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
