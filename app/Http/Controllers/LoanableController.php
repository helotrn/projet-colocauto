<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Loanable;
use App\Repositories\LoanableRepository;
use Validator;

class LoanableController extends RestController
{
    protected $bikeController;

    public function __construct(LoanableRepository $repository, Loanable $model, BikeController $bikeController) {
        $this->repo = $repository;
        $this->model = $model;
        $this->bikeController = $bikeController;
    }

    public function index(Request $request) {
        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->getErrors(), $e->getMessage());
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
