<?php

namespace App\Repositories;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Keyword;

class KeywordRepository extends RestRepository
{
    public function __construct(Keyword $model) {
        $this->model = $model;
    }
}
