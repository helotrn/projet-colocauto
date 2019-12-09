<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Tag;
use App\Repositories\TagRepository;

class TagController extends RestController
{
    public function __construct(TagRepository $repository, Tag $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
