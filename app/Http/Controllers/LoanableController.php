<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Loanable;
use App\Repositories\LoanableRepository;
use Illuminate\Validation\ValidationException;
use Validator;

class LoanableController extends RestController
{
    protected $bikeController;
    protected $carController;
    protected $trailerController;

    public function __construct(
        LoanableRepository $repository,
        Loanable $model,
        BikeController $bikeController,
        CarController $carController,
        TrailerController $trailerController
    ) {
        $this->repo = $repository;
        $this->model = $model;
        $this->bikeController = $bikeController;
        $this->carController = $carController;
        $this->trailerController = $trailerController;
    }

    public function create(Request $request) {

        try {
            $item = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item, 201);
    }

    public function index(Request $request) {
        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithCollection($request, $items, $total);
    }

    public function retrieve(Request $request, $id) {
        $item = $this->repo->find($request, $id);

        switch ($item->type) {
            case 'bike':
                $bikeRequest = new Request();
                $bikeRequest->setMethod('GET');
                $bikeRequest->request->add($request->all());
                return $this->bikeController->retrieve($bikeRequest, $id);
            case 'car':
                $carRequest = new Request();
                $carRequest->setMethod('GET');
                $carRequest->request->add($request->all());
                return $this->carController->retrieve($carRequest, $id);
            case 'trailer':
                $trailerRequest = new Request();
                $trailerRequest->setMethod('GET');
                $trailerRequest->request->add($request->all());
                return $this->trailerController->retrieve($trailerRequest, $id);
            default:
                throw new \Exception('invalid loanable type');
        }
    }

    public function update(Request $request, $id) {
        $validator = Validator::make(
            $request->all(),
            [
                'rule' => 'one_of:bike,car,trailer',
            ]
        );

        if ($validator->fails()) {
            return $this->respondWithErrors($validator->errors());
        }

        switch ($request->get('type')) {
            case 'bike':
                $bikeRequest = new Request();
                $bikeRequest->setMethod('POST');
                $bikeRequest->request->add($request->all());
                return $this->bikeController->update($bikeRequest, $id);
            case 'car':
                $carRequest = new Request();
                $carRequest->setMethod('POST');
                $carRequest->request->add($request->all());
                return $this->carController->update($carRequest, $id);
            case 'trailer':
                $trailerRequest = new Request();
                $trailerRequest->setMethod('POST');
                $trailerRequest->request->add($request->all());
                return $this->trailerController->update($trailerRequest, $id);
            default:
                throw new \Exception('invalid loanable type');
        }
    }

    public function template(Request $request) {
        return [
          'item' => [
            'name' => '',
            'type' => null,
          ],
          'filters' => $this->model::$filterTypes,
        ];
    }
}
