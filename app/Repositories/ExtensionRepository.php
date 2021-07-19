<?php

namespace App\Repositories;

use App\Models\Extension;

class ExtensionRepository extends RestRepository
{
    public function __construct(Extension $model) {
        $this->model = $model;
    }
}
