<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Community;
use App\Repositories\CommunityRepository;

class CommunityController extends RestController
{
    public function __construct(CommunityRepository $repository, Community $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
