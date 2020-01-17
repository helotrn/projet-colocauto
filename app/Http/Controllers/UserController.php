<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\User\UpdateRequest;
use App\Models\User;
use App\Repositories\UserRepository;

class UserController extends RestController
{
    public function __construct(UserRepository $repository, User $model) {
        $this->repo = $repository;
        $this->model = $model;
    }

    public function index(Request $request) {
        $perPage = $request->get('per_page') ?: 10;
        $page = $request->get('page') ?: 1;

        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->getErrors(), $e->getMessage());
        }

        return $this->respondWithCollection($request, $items);
    }

    public function create(Request $request) {
        return $this->validateAndCreate($request);
    }

    public function update(UpdateRequest $request, $id) {
        return parent::validateAndUpdate($request, $id);
    }

    public function retrieve(Request $request, $id) {
        $item = $this->repo->find($request, $id);

        return $this->respondWithItem($request, $item);
    }

    public function delete(Request $request, $id) {
        $item = $this->repo->find($request, $id);

        if ($item->delete()) {
            return $this->respondWithItem($request, $item);
        }

        return $this->respondWithErrors('Error deleting', 400);
    }
}
