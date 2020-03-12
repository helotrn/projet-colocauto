<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Loan;
use App\Repositories\LoanRepository;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class LoanController extends RestController
{
    public function __construct(LoanRepository $repository, Loan $model) {
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

    public function create(Request $request) {
        try {
            $item = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item, 201);
    }

    public function update(Request $request, $id) {
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

    public function destroy(Request $request, $id) {
        try {
            $response = parent::validateAndDestroy($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }

    public function template(Request $request) {
        $defaultDeparture = new Carbon;
        $defaultDeparture->minute = floor($defaultDeparture->minute / 10) * 10;
        $defaultDeparture->second = 0;

        return [
            'item' => [
                'departure_at' => $defaultDeparture->format('Y-m-d H:i:s'),
                'duration_in_minutes' => 60,
                'estimated_distance' => 10,
                'estimated_price' => 0,
                'message_for_owner' => '',
                'reason' => '',
                'incidents' => [],
                'actions' => [],
            ],
            'form' => [
                'departure_at' => [
                    'type' => 'date',
                ],
                'duration_in_minutes' => [
                    'type' => 'number',
                ],
                'estimated_price' => [
                    'type' => 'number',
                ],
                'message_for_owner' => [
                  'type' => 'textarea',
                ],
                'reason' => [
                  'type' => 'textarea',
                ],
            ],
            'filters' => $this->model::$filterTypes,
        ];
    }
}
