<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\PrePayment;
use App\Repositories\LoanRepository;
use App\Repositories\PrePaymentRepository;
use Illuminate\Validation\ValidationException;

class PrePaymentController extends RestController
{
    public function __construct(
        PrePaymentRepository $repository,
        PrePayment $model,
        LoanRepository $loanRepository
    ) {
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
        $item = $this->repo->find(
            $request->redirectAuth(Request::class),
            $id
        );

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
        $authRequest = $request->redirectAuth(Request::class);

        $item = $this->repo->find($authRequest, $actionId);
        $loan = $this->loanRepo->find($authRequest, $loanId);

        $item->status = 'completed';
        $item->save();

        return response('', 201);
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
