<?php

namespace App\Repositories;

use App\Models\Report;

class ReportRepository extends RestRepository
{
    public function __construct(Report $model)
    {
        $this->model = $model;
    }
}
