<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\User\CreateRequest;
use App\Http\Requests\User\DestroyRequest;
use App\Http\Requests\User\IndexRequest;
use App\Http\Requests\User\RetrieveRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Models\User;
use App\Repositories\UserRepository;

class UserController extends RestController
{
    public function __construct(UserRepository $repository, User $model) {
        $this->repo = $repository;
        $this->model = $model;
    }

    public function index(IndexRequest $request) {
        $perPage = $request->get('per_page') ?: 10;
        $page = $request->get('page') ?: 1;

        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->getErrors(), $e->getMessage());
        }
        return $this->respondWithCollection($request, $items, $total);
    }

    public function create(CreateRequest $request) {
        try {
            $response = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->getErrors(), $e->getMessage());
        }
        return $response;
    }

    public function update(UpdateRequest $request, $id) {
        try {
            $response = parent::validateAndUpdate($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->getErrors(), $e->getMessage());
        }
        return $response;
    }

    public function retrieve(RetrieveRequest $request, $id) {
        if ($id === 'me') {
            $id = $request->user()->id;
        }

        $item = $this->repo->find($request, $id);

        try {
            $response = $this->respondWithItem($request, $item);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->getErrors(), $e->getMessage());
        }
        return $response;
    }

    public function destroy(DestroyRequest $request, $id) {
        try {
            $response = parent::validateAndDestroy($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->getErrors(), $e->getMessage());
        }
        return $response;
    }
}
