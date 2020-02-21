<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Intention;
use App\Repositories\IntentionRepository;
use App\Repositories\LoanRepository;
use Illuminate\Validation\ValidationException;

class IntentionController extends RestController
{
    public function __construct(IntentionRepository $repository, Intention $model, LoanRepository $loanRepository) {
        $this->repo = $repository;
        $this->model = $model;
        $this->loanRepo = $loanRepository;
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

    public function complete(Request $request, $actionId, $loanId) {
        $item = $this->repo->find($request, $actionId);
        $loan = $this->loanRepo->find($request, $loanId);
        if ($item->id && $loan->id) {
            $item->status = 'completed';
            $item->save();
        }
    }

    public function cancel(Request $request, $actionId, $loanId) {
        $item = $this->repo->find($request, $actionId);
        $loan = $this->loanRepo->find($request, $loanId);
        if ($item->id && $loan->id) {
            $item->status = 'canceled';
            $item->save();
        }
    }
}
