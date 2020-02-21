<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Action;
use App\Repositories\ActionRepository;
use App\Repositories\LoanRepository;
use Validator;

class ActionController extends RestController
{

    public function __construct(ActionRepository $repository, Action $model, PaymentController $paymentController, TakeoverController $takeoverController, HandoverController $handoverController, IncidentController $incidentController, IntentionController $intentionController, ExtensionController $extensionController) {
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
