<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Incident;
use App\Repositories\IncidentRepository;

class IncidentController extends RestController
{
    public function __construct(IncidentRepository $repository, Incident $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
