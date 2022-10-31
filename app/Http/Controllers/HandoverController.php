<?php

namespace App\Http\Controllers;

use App\Events\LoanHandoverContestationResolvedEvent;
use App\Events\LoanHandoverContestedEvent;
use App\Http\Controllers\LoanController;
use App\Http\Requests\Action\HandoverRequest;
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

    public function complete(HandoverRequest $request, $actionId, $loanId)
    {
        $authRequest = $request->redirectAuth(Request::class);

        $item = $this->repo->find($authRequest, $actionId);
        $loan = $this->loanRepo->find($authRequest, $loanId);

        if ($item->isCompleted()) {
            return $this->respondWithErrors([
                "status" => __("validation.custom.status.action_completed"),
            ]);
        }

        $item->fill($request->all());
        $item->comments_on_contestation = "";

        $item->complete();
        $item->save();

        // Move forward if possible.
        LoanController::loanActionsForward($loan);

        $this->repo->update($request, $actionId, $request->all());

        if ($item->isContested()) {
            event(
                new LoanHandoverContestationResolvedEvent(
                    $item,
                    $request->user()
                )
            );
        }

        return $item;
    }

    public function cancel(Request $request, $actionId, $loanId)
    {
        $authRequest = $request->redirectAuth(Request::class);

        $item = $this->repo->find($authRequest, $actionId);
        $loan = $this->loanRepo->find($authRequest, $loanId);

        $item->comments_on_contestation = $request->get(
            "comments_on_contestation"
        );
        $item->contest();

        $item->save();

        event(new LoanHandoverContestedEvent($item, $request->user()));

        return $item;
    }
}
