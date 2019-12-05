<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Padlock;
use App\Repositories\PadlockRepository;

class PadlockController extends RestController
{
    public function __construct(PadlockRepository $repository, Padlock $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
