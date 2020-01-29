<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\Community\CreateRequest;
use App\Http\Requests\Community\DestroyRequest;
use App\Http\Requests\Community\IndexRequest;
use App\Http\Requests\Community\RetrieveRequest;
use App\Http\Requests\Community\UpdateRequest;
use App\Models\Community;
use App\Repositories\CommunityRepository;

class CommunityController extends RestController
{
    public function __construct(CommunityRepository $repository, Community $model) {
        $this->repo = $repository;
        $this->model = $model;
    }

    public function index(IndexRequest $request) {
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
