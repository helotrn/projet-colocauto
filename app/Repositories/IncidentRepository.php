<?php

namespace App\Repositories;

use App\Models\Incident;
use Molotov\RestRepository;

class IncidentRepository extends RestRepository
{
    public function __construct(Incident $model) {
        $this->model = $model;
    }
}
