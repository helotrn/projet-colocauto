<?php

namespace App\Repositories;

use App\Models\Extension;
use Molotov\Repositories\RestRepository;

class ExtensionRepository extends RestRepository
{
    public function __construct(Extension $model) {
        $this->model = $model;
    }
}
