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

    public function index(Request $request) {
        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithCollection($request, $items, $total);
    }
}
