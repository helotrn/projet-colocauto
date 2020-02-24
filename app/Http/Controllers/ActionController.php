<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Action;
use App\Repositories\ActionRepository;
use App\Repositories\LoanRepository;
use Validator;

class ActionController extends RestController
{

    public function __construct(
        ActionRepository $repository,
        Action $model,
        PaymentController $paymentController,
        TakeoverController $takeoverController,
        HandoverController $handoverController,
        IncidentController $incidentController,
        IntentionController $intentionController,
        ExtensionController $extensionController
    ) {
        $this->repo = $repository;
        $this->model = $model;
        $this->paymentController = $paymentController;
        $this->takeoverController = $takeoverController;
        $this->handoverController = $handoverController;
        $this->incidentController = $incidentController;
        $this->intentionController = $intentionController;
        $this->extensionController = $extensionController;
    }

    public function index(Request $request) {
        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->getErrors(), $e->getMessage());
        }

        return $this->respondWithCollection($request, $items, $total);
    }
    // payment, takeover, handover, incident, intention, extension
    public function retrieve(Request $request, $id) {
        $item = $this->repo->find($request, $id);

        switch ($item->type) {
            case 'intention':
                $intentionRequest = new Request();
                $intentionRequest->setMethod('GET');
                $intentionRequest->request->add($request->all());
                return $this->intentionController->retrieve($intentionRequest, $id);
            case 'payment':
                $paymentRequest = new Request();
                $paymentRequest->setMethod('GET');
                $paymentRequest->request->add($request->all());
                return $this->paymentController->retrieve($paymentRequest, $id);
            case 'takeover':
                $takeoverRequest = new Request();
                $takeoverRequest->setMethod('GET');
                $takeoverRequest->request->add($request->all());
                return $this->takeoverController->retrieve($takeoverRequest, $id);
            case 'extension':
                $extensionRequest = new Request();
                $extensionRequest->setMethod('GET');
                $extensionRequest->request->add($request->all());
                return $this->extensionController->retrieve($extensionRequest, $id);
            case 'intention':
                $intentionRequest = new Request();
                $intentionRequest->setMethod('GET');
                $intentionRequest->request->add($request->all());
                return $this->intentionController->retrieve($intentionRequest, $id);
            case 'incident':
                $incidentRequest = new Request();
                $incidentRequest->setMethod('GET');
                $incidentRequest->request->add($request->all());
                return $this->incidentController->retrieve($incidentRequest, $id);
            default:
                throw new \Exception('invalid action type');
        }
    }

    public function update(Request $request, $id) {
        $validator = Validator::make(
            $request->all(),
            [
                'rule' => 'one_of:payment,takeover,handover,incident,intention,extension',
            ]
        );

        if ($validator->fails()) {
            return $this->respondWithErrors($validator->errors());
        }

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

    public function complete(Request $request, $loanId, $actionId) {
        $item = $this->repo->find($request, $actionId);

        switch ($item->type) {
            case 'intention':
                $intentionRequest = new Request();
                $intentionRequest->setMethod('PUT');
                $intentionRequest->request->add($request->all());
                return $this->intentionController->complete($intentionRequest, $actionId, $loanId);
            case 'payment':
                $paymentRequest = new Request();
                $paymentRequest->setMethod('PUT');
                $paymentRequest->request->add($request->all());
                return $this->paymentController->complete($paymentRequest, $actionId, $loanId);
            case 'takeover':
                $takeoverRequest = new Request();
                $takeoverRequest->setMethod('PUT');
                $takeoverRequest->request->add($request->all());
                return $this->takeoverController->complete($takeoverRequest, $actionId, $loanId);
            case 'extension':
                $extensionRequest = new Request();
                $extensionRequest->setMethod('PUT');
                $extensionRequest->request->add($request->all());
                return $this->extensionController->complete($extensionRequest, $actionId, $loanId);
            case 'handover':
                $handoverRequest = new Request();
                $handoverRequest->setMethod('PUT');
                $handoverRequest->request->add($request->all());
                return $this->handoverController->complete($handoverRequest, $actionId, $loanId);
            case 'incident':
                $incidentRequest = new Request();
                $incidentRequest->setMethod('PUT');
                $incidentRequest->request->add($request->all());
                return $this->incidentController->complete($incidentRequest, $actionId, $loanId);
            default:
                throw new \Exception('invalid action type');
        }
    }

    public function cancel(Request $request, $loanId, $actionId) {
        $item = $this->repo->find($request, $actionId);

        switch ($item->type) {
            case 'intention':
                $intentionRequest = new Request();
                $intentionRequest->setMethod('PUT');
                $intentionRequest->request->add($request->all());
                return $this->intentionController->cancel($intentionRequest, $actionId, $loanId);
            case 'payment':
                $paymentRequest = new Request();
                $paymentRequest->setMethod('PUT');
                $paymentRequest->request->add($request->all());
                return $this->paymentController->complete($paymentRequest, $actionId, $loanId);
            case 'takeover':
                $takeoverRequest = new Request();
                $takeoverRequest->setMethod('PUT');
                $takeoverRequest->request->add($request->all());
                return $this->takeoverController->complete($takeoverRequest, $actionId, $loanId);
            case 'extension':
                $extensionRequest = new Request();
                $extensionRequest->setMethod('PUT');
                $extensionRequest->request->add($request->all());
                return $this->extensionController->complete($extensionRequest, $actionId, $loanId);
            case 'handover':
                $handoverRequest = new Request();
                $handoverRequest->setMethod('PUT');
                $handoverRequest->request->add($request->all());
                return $this->handoverController->complete($handoverRequest, $actionId, $loanId);
            case 'incident':
                $incidentRequest = new Request();
                $incidentRequest->setMethod('PUT');
                $incidentRequest->request->add($request->all());
                return $this->incidentController->complete($incidentRequest, $actionId, $loanId);
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
