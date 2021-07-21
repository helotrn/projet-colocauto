<?php

namespace App\Http\Controllers;

use App\Events\LoanableCreatedEvent;
use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\Car\CreateRequest;
use App\Http\Requests\Loanable\DestroyRequest as LoanableDestroyRequest;
use App\Http\Requests\Car\UpdateRequest;
use App\Models\Car;
use App\Repositories\CarRepository;
use Illuminate\Validation\ValidationException;

class CarController extends RestController
{
    public function __construct(CarRepository $repository, Car $model)
    {
        $this->repo = $repository;
        $this->model = $model;
    }

    public function index(Request $request)
    {
        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithCollection($request, $items, $total);
    }

    public function create(CreateRequest $request)
    {
        try {
            $item = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        event(new LoanableCreatedEvent($request->user(), $item));

        return $this->respondWithItem($request, $item, 201);
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            $item = parent::validateAndUpdate($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item);
    }

    public function retrieve(Request $request, $id)
    {
        $item = $this->repo->find($request, $id);

        try {
            $response = $this->respondWithItem($request, $item);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }

    public function destroy(LoanableDestroyRequest $request, $id)
    {
        try {
            $response = parent::validateAndDestroy($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }

    public function restore(Request $request, $id)
    {
        try {
            $response = parent::validateAndRestore($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }
}
