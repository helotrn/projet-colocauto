<?php

namespace App\Repositories;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Community;

class CommunityRepository extends RestRepository
{
    public function __construct(Community $model) {
        $this->model = $model;
    }
}
