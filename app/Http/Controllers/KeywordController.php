<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Keyword;
use App\Repositories\KeywordRepository;

class KeywordController extends RestController
{
    public function __construct(KeywordRepository $repository, Keyword $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
