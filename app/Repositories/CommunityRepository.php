<?php

namespace App\Repositories;

use App\Models\Community;
use Molotov\Repositories\RestRepository;

class CommunityRepository extends RestRepository
{
    public function __construct(Community $model) {
        $this->model = $model;
        $this->columnsDefinition = $model::getColumnsDefinition();
    }
}
