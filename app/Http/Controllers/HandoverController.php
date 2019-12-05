<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Handover;
use App\Repositories\HandoverRepository;

class HandoverController extends RestController
{
    public function __construct(HandoverRepository $repository, Handover $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
