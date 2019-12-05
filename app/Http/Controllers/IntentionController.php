<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Intention;
use App\Repositories\IntentionRepository;

class IntentionController extends RestController
{
    public function __construct(IntentionRepository $repository, Intention $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
