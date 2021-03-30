<?php

namespace App\Http\Controllers;

use App\Events\LoanableCreatedEvent;
use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\Bike\CreateRequest;
use App\Http\Requests\Bike\DestroyRequest;
use App\Http\Requests\Bike\UpdateRequest;
use App\Models\Bike;
use App\Repositories\BikeRepository;
use Illuminate\Validation\ValidationException;

class BikeController extends RestController
{
    public function __construct(BikeRepository $repository, Bike $model) {
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

    public function create(CreateRequest $request) {
        try {
            $item = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        event(new LoanableCreatedEvent($request->user(), $item));

        return $this->respondWithItem($request, $item, 201);
    }

    public function update(UpdateRequest $request, $id) {
        try {
            $item = parent::validateAndUpdate($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item);
    }

    public function retrieve(Request $request, $id) {
        $item = $this->repo->find($request, $id);

        try {
            $response = $this->respondWithItem($request, $item);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }

    public function destroy(DestroyRequest $request, $id) {
        try {
            $response = parent::validateAndDestroy($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }

    public function restore(Request $request, $id) {
        try {
            $response = parent::validateAndRestore($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }
}
