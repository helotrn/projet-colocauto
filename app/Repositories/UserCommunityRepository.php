<?php

namespace App\Repositories;

use App\Http\Requests\BaseRequest as Request;
use App\Models\UserCommunity;

class UserCommunityRepository extends RestRepository
{
    public function __construct(UserCommunity $model) {
        $this->model = $model;
        $this->columnsDefinition = $model::getColumnsDefinition();
    }
}
