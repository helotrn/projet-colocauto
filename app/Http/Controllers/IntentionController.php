<?php

namespace App\Http\Controllers;

use App\Events\LoanIntentionAcceptedEvent;
use App\Events\LoanIntentionRejectedEvent;
use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\Action\IntentionRequest;
use App\Models\Intention;
use App\Repositories\IntentionRepository;
use App\Repositories\LoanRepository;
use Illuminate\Validation\ValidationException;

class IntentionController extends RestController
{
    public function __construct(
        IntentionRepository $repository,
        Intention $model,
        LoanRepository $loanRepository
    ) {
        $this->repo = $repository;
        $this->model = $model;
        $this->loanRepo = $loanRepository;
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

    public function create(Request $request)
    {
        try {
            $item = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item, 201);
    }

    public function update(Request $request, $id)
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

    public function destroy(Request $request, $id)
    {
        try {
            $response = parent::validateAndDestroy($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }

    public function complete(IntentionRequest $request, $actionId, $loanId)
    {
        $authRequest = $request->redirectAuth(Request::class);
        $item = $this->repo->find($authRequest, $actionId);

        $item->message_for_borrower = $request->get("message_for_borrower");
        $item->status = "completed";
        $item->save();

        event(new LoanIntentionAcceptedEvent($item));

        return $item;
    }

    public function cancel(Request $request, $actionId, $loanId)
    {
        $authRequest = $request->redirectAuth(Request::class);
        $item = $this->repo->find($authRequest, $actionId);

        $item->message_for_borrower = $request->get("message_for_borrower");

        $item->cancel();
        $item->save();

        event(new LoanIntentionRejectedEvent($item));

        return $item;
    }
}
