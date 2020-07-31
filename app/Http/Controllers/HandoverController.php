<?php

namespace App\Http\Controllers;

use App\Http\Requests\Action\PaymentRequest;
use App\Http\Requests\BaseRequest as Request;
use App\Models\Handover;
use App\Repositories\HandoverRepository;
use App\Repositories\LoanRepository;
use Illuminate\Validation\ValidationException;

class HandoverController extends RestController
{
    public function __construct(
        HandoverRepository $repository,
        Handover $model,
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
        $authRequest = $request->redirectAuth(Request::class);

        $item = $this->repo->find($authRequest, $actionId);
        $loan = $this->loanRepo->find($authRequest, $loanId);

        $item->fill($request->all());
        $item->status = 'completed';
        $item->save();

        $this->repo->update($request, $actionId, $request->all());

        if (!$loan->loanable->owner) {
            $payment = $loan->payment()->first();
            $paymentController = app(PaymentController::class);
            $paymentController->complete(
                $request->redirect(PaymentRequest::class),
                $payment->id,
                $loanId
            );
        }

        return $item;
    }

    public function cancel(Request $request, $actionId, $loanId) {
        $authRequest = $request->redirectAuth(Request::class);

        $item = $this->repo->find($authRequest, $actionId);
        $loan = $this->loanRepo->find($authRequest, $loanId);

        $item->fill($request->all());
        $item->status = 'canceled';
        $item->save();

        return $item;
    }
}
