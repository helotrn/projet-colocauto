<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Bike;
use App\Repositories\BikeRepository;
use Illuminate\Validation\ValidationException;

class BikeController extends RestController
{
    public function __construct(BikeRepository $repository, Bike $model) {
        $this->repo = $repository;
        $this->model = $model;
    }

    public function retrieve(Request $request, $id) {
        $item = $this->repo->find($request, $id);

        try {
            $response = $this->respondWithItem($request, $item);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->getErrors(), $e->getMessage());
        }

        return $response;
    }


    public function update(Request $request, $id) {
        try {
            $item = parent::validateAndUpdate($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->getErrors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item);
    }
}
