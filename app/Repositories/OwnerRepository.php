<?php

namespace App\Repositories;

use App\Models\Owner;
use Molotov\Repositories\RestRepository;

class OwnerRepository extends RestRepository
{
    public function __construct(Owner $model) {
        $this->model = $model;
    }
}
