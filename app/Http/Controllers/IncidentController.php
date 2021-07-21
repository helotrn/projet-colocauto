<?php

namespace App\Http\Controllers;

use App\Events\LoanIncidentCreatedEvent;
use App\Events\LoanIncidentResolvedEvent;
use App\Http\Requests\Action\ExtensionRequest;
use App\Http\Requests\Action\IncidentRequest;
use App\Http\Requests\BaseRequest as Request;
use App\Models\Incident;
use App\Repositories\IncidentRepository;
use Illuminate\Validation\ValidationException;

class IncidentController extends RestController
{
    public function __construct(IncidentRepository $repository, Incident $model)
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

    public function create(Request $request)
    {
        try {
            $item = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        event(new LoanIncidentCreatedEvent($item));

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

    public function complete(IncidentRequest $request, $actionId, $loanId)
    {
        $authRequest = $request->redirectAuth(Request::class);
        $item = $this->repo->find($authRequest, $actionId);

        $item->status = "completed";
        $item->save();

        event(new LoanIncidentResolvedEvent($item));

        return $item;
    }

    public function cancel(IncidentRequest $request, $actionId, $loanId)
    {
        $authRequest = $request->redirectAuth(Request::class);
        $item = $this->repo->find($authRequest, $actionId);

        $item->status = "canceled";
        $item->save();

        event(new LoanIncidentRejectedEvent($item));

        return $item;
    }
}
