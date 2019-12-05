<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Action;
use App\Repositories\ActionRepository;

class ActionController extends RestController
{
    public function __construct(ActionRepository $repository, Action $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
