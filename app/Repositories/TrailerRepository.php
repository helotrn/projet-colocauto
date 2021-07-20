<?php

namespace App\Repositories;

use App\Models\Trailer;

class TrailerRepository extends RestRepository
{
    public function __construct(Trailer $model) {
        $this->model = $model;
        $this->columnsDefinition = $model::getColumnsDefinition();
    }
}
