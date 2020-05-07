<?php

namespace App\Http\Controllers;

use App\Events\LoanExtensionCreatedEvent;
use App\Http\Requests\Action\ExtensionRequest;
use App\Http\Requests\BaseRequest as Request;
use App\Models\Extension;
use App\Repositories\ExtensionRepository;

class ExtensionController extends RestController
{
    public function __construct(ExtensionRepository $repository, Extension $model) {
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

    public function create(ExtensionRequest $request) {
        try {
            $item = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        event(new LoanExtensionCreatedEvent($item));

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

    public function complete(ExtensionRequest $request, $actionId, $loanId) {
        $authRequest = $request->redirectAuth(Request::class);
        $item = $this->repo->find($authRequest, $actionId);

        $item->message_for_borrower = $request->get('message_for_borrower');
        $item->status = 'completed';
        $item->save();

        return $item;
    }

    public function cancel(Request $request, $actionId, $loanId) {
        $authRequest = $request->redirectAuth(Request::class);
        $item = $this->repo->find($authRequest, $actionId);

        $item->message_for_borrower = $request->get('message_for_borrower');
        $item->status = 'canceled';
        $item->save();

        return $item;
    }
}
