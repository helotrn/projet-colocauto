<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Takeover;
use App\Repositories\TakeoverRepository;

class TakeoverController extends RestController
{
    public function __construct(TakeoverRepository $repository, Takeover $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
