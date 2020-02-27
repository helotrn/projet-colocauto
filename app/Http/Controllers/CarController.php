<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\Car\CreateRequest;
use App\Http\Requests\Car\UpdateRequest;
use App\Models\Car;
use App\Repositories\CarRepository;
use Illuminate\Validation\ValidationException;

class CarController extends RestController
{
    public function __construct(CarRepository $repository, Car $model) {
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

        $fieldsRequest = new Request;
        $fieldsRequest->merge($request->get('fields'));
        $fullItem = $this->repo->find($fieldsRequest, $item->id);

        return $this->respondWithItem($request, $fullItem, 201);
    }

    public function update(UpdateRequest $request, $id) {
        try {
            $item = parent::validateAndUpdate($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        $fieldsRequest = new Request;
        $fieldsRequest->merge([ 'fields' => $request->get('fields') ]);
        $fullItem = $this->repo->find($fieldsRequest, $item->id);

        return $this->respondWithItem($request, $fullItem);
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

    public function destroy(Request $request, $id) {
        try {
            $response = parent::validateAndDestroy($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }
}
