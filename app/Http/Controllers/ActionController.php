<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\Action\ActionRequest;
use App\Http\Requests\Action\CreateRequest;
use App\Http\Requests\Action\ExtensionRequest;
use App\Http\Requests\Action\HandoverRequest;
use App\Http\Requests\Action\IntentionRequest;
use App\Http\Requests\Action\PaymentRequest;
use App\Http\Requests\Action\PrePaymentRequest;
use App\Http\Requests\Action\TakeoverRequest;
use App\Models\Action;
use App\Repositories\ActionRepository;
use App\Repositories\LoanRepository;

class ActionController extends RestController
{
    public function __construct(
        ActionRepository $repository,
        Action $model,
        PrePaymentController $prePaymentController,
        PaymentController $paymentController,
        TakeoverController $takeoverController,
        HandoverController $handoverController,
        IncidentController $incidentController,
        IntentionController $intentionController,
        ExtensionController $extensionController,
        LoanRepository $loanRepository
    ) {
        $this->repo = $repository;
        $this->model = $model;
        $this->prePaymentController = $prePaymentController;
        $this->paymentController = $paymentController;
        $this->takeoverController = $takeoverController;
        $this->handoverController = $handoverController;
        $this->incidentController = $incidentController;
        $this->intentionController = $intentionController;
        $this->extensionController = $extensionController;
        $this->loanRepository = $loanRepository;
    }

    public function create(CreateRequest $request) {
        switch ($request->get('type')) {
            case 'extension':
                return $this->extensionController
                    ->create($request->redirect(ExtensionRequest::class));
            case 'incident':
                return $this->incidentController->create($request);
            default:
                throw new \Exception('invalid action type');
        }
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
            case 'intention':
                return $this->intentionController->retrieve($request, $id);
            case 'pre_payment':
                return $this->prePaymentController->retrieve($request, $id);
            case 'payment':
                return $this->paymentController->retrieve($request, $id);
            case 'takeover':
                return $this->takeoverController->retrieve($request, $id);
            case 'extension':
                return $this->extensionController->retrieve($request, $id);
            case 'intention':
                return $this->intentionController->retrieve($request, $id);
            case 'incident':
                return $this->incidentController->retrieve($request, $id);
            default:
                throw new \Exception('invalid action type');
        }
    }

    public function update(ActionRequest $request, $id) {
        switch ($request->get('type')) {
            case 'intention':
                $intentionRequest = new Request();
                $intentionRequest->setMethod('POST');
                $intentionRequest->request->add($request->all());
                return $this->intentionController->update($intentionRequest, $id);
            case 'payment':
                $paymentRequest = new Request();
                $paymentRequest->setMethod('POST');
                $paymentRequest->request->add($request->all());
                return $this->paymentController->update($paymentRequest, $id);
            case 'takeover':
                $takeoverRequest = new Request();
                $takeoverRequest->setMethod('POST');
                $takeoverRequest->request->add($request->all());
                return $this->takeoverController->update($takeoverRequest, $id);
            case 'extension':
                $extensionRequest = new Request();
                $extensionRequest->setMethod('POST');
                $extensionRequest->request->add($request->all());
                return $this->extensionController->update($extensionRequest, $id);
            case 'handover':
                $handoverRequest = new Request();
                $handoverRequest->setMethod('POST');
                $handoverRequest->request->add($request->all());
                return $this->handoverController->update($handoverRequest, $id);
            case 'incident':
                $incidentRequest = new Request();
                $incidentRequest->setMethod('POST');
                $incidentRequest->request->add($request->all());
                return $this->incidentController->update($incidentRequest, $id);
            default:
                throw new \Exception('invalid action type');
        }
    }

    public function complete(ActionRequest $request, $loanId, $actionId) {
        switch ($request->get('type')) {
            case 'intention':
                $intentionRequest = $request->redirect(IntentionRequest::class);
                return $this->intentionController->complete(
                    $intentionRequest,
                    $actionId,
                    $loanId
                );
            case 'pre_payment':
                $prePaymentRequest = $request->redirect(PrePaymentRequest::class);
                return $this->prePaymentController->complete(
                    $prePaymentRequest,
                    $actionId,
                    $loanId
                );
            case 'takeover':
                $takeoverRequest = $request->redirect(TakeoverRequest::class);
                return $this->takeoverController->complete(
                    $takeoverRequest,
                    $actionId,
                    $loanId
                );
            case 'handover':
                $handoverRequest = $request->redirect(HandoverRequest::class);
                return $this->handoverController->complete(
                    $handoverRequest,
                    $actionId,
                    $loanId
                );
            case 'extension':
                $extensionRequest = $request->redirect(ExtensionRequest::class);
                return $this->extensionController->complete(
                    $extensionRequest,
                    $actionId,
                    $loanId
                );
            case 'incident':
                $incidentRequest = new Request();
                $incidentRequest->setMethod('PUT');
                $incidentRequest->request->add($request->all());
                return $this->incidentController->complete($incidentRequest, $actionId, $loanId);
            case 'payment':
                $paymentRequest = $request->redirect(PaymentRequest::class);
                return $this->paymentController->complete(
                    $paymentRequest,
                    $actionId,
                    $loanId
                );
            default:
                throw new \Exception('invalid action type');
        }
    }

    public function cancel(ActionRequest $request, $loanId, $actionId) {
        switch ($request->get('type')) {
            case 'intention':
                return $this->intentionController->cancel($request, $actionId, $loanId);
            case 'pre_payment':
                return $this->prePaymentController->cancel($request, $actionId, $loanId);
            case 'payment':
                return $this->paymentController->cancel($request, $actionId, $loanId);
            case 'takeover':
                return $this->takeoverController->cancel($request, $actionId, $loanId);
            case 'extension':
                return $this->extensionController->cancel($request, $actionId, $loanId);
            case 'handover':
                return $this->handoverController->cancel($request, $actionId, $loanId);
            case 'incident':
                return $this->incidentController->cancel($request, $actionId, $loanId);
            default:
                throw new \Exception('invalid action type');
        }
    }

    public function template(Request $request) {
        return [
          'item' => [
            'status' => '',
            'type' => null,
          ],
          'filters' => $this->model::$filterTypes,
        ];
    }
}
