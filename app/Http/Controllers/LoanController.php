<?php

namespace App\Http\Controllers;

use App\Events\LoanCreatedEvent;
use App\Exports\LoansExport;
use App\Http\Requests\Action\ActionRequest;
use App\Http\Requests\Action\CreateRequest as ActionCreateRequest;
use App\Http\Requests\Loan\CreateRequest;
use App\Http\Requests\BaseRequest as Request;
use App\Models\Loan;
use App\Repositories\LoanRepository;
use Carbon\Carbon;
use Excel;
use Illuminate\Validation\ValidationException;

class LoanController extends RestController
{
    private $actionController;

    public function __construct(
        LoanRepository $repository,
        Loan $model,
        ActionController $actionController
    ) {
        $this->repo = $repository;
        $this->model = $model;
        $this->actionController = $actionController;
    }

    public function index(Request $request) {
        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        switch ($request->headers->get('accept')) {
            case 'text/csv':
                $filename = $this->respondWithCsv($request, $items, $this->model);
                $base = app()->make('url')->to('/');
                return response($base . $filename, 201);
            default:
                return $this->respondWithCollection($request, $items, $total);
        }
    }

    public function create(CreateRequest $request) {
        try {
            $item = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        event(new LoanCreatedEvent($request->user(), $item));

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

    public function cancel(Request $request, $id) {
        $item = $this->repo->find($request, $id);

        $lastAction = $item->actions->last();
        $request->merge([ 'type' => $lastAction->type ]);

        $lastAction = $this->actionController->cancel(
            $request->redirect(ActionRequest::class, $request),
            $id,
            $lastAction->id
        );

        try {
            $response = $this->respondWithItem($request, $item);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }

    public function retrieveBorrower(Request $request, $loanId) {
        $item = $this->repo->find($request, $loanId);

        return $this->respondWithItem($request, $item->borrower);
    }

    public function retrieveAction(Request $request, $loanId, $actionId) {
        $request->merge([ 'loan_id' => $loanId ]);
        return $this->actionController->retrieve($request, $actionId);
    }

    public function createAction(ActionCreateRequest $request, $id) {
        $item = $this->repo->find($request->redirectAuth(Request::class), $id);

        $request->merge([ 'loan_id' => $id ]);

        return $this->actionController->create($request);
    }

    public function template(Request $request) {
        $defaultDeparture = new Carbon;
        $defaultDeparture->minute = floor($defaultDeparture->minute / 10) * 10;
        $defaultDeparture->second = 0;

        $template = [
            'item' => [
                'departure_at' => $defaultDeparture->format('Y-m-d H:i:s'),
                'duration_in_minutes' => 60,
                'estimated_distance' => 10,
                'estimated_price' => 0,
                'platform_tip' => 0,
                'message_for_owner' => '',
                'reason' => '',
                'incidents' => [],
                'actions' => [],
                'community_id' => null,
                'community' => null,
                'borrower_id' => null,
                'borrower' => null,
                'loanable_id' => null,
                'loanable' => null,
            ],
            'form' => [
                'departure_at' => [
                    'type' => 'datetime',
                ],
                'duration_in_minutes' => [
                    'type' => 'number',
                ],
                'estimated_distance' => [
                    'type' => 'number',
                ],
                'estimated_insurance' => [
                    'type' => 'number',
                ],
                'estimated_price' => [
                    'type' => 'number',
                ],
                'platform_tip' => [
                    'type' => 'number',
                ],
                'message_for_owner' => [
                  'type' => 'textarea',
                ],
                'reason' => [
                  'type' => 'textarea',
                ],
                'community_id' => [
                    'type' => 'relation',
                    'query' => [
                        'slug' => 'communities',
                        'value' => 'id',
                        'text' => 'name',
                        'params' => [
                            'fields' => 'id,name',
                        ],
                    ],
                ],
                'loanable_id' => [
                    'type' => 'relation',
                    'query' => [
                        'slug' => 'loanables',
                        'value' => 'id',
                        'text' => 'name',
                        'params' => [
                            'fields' => 'id,name',
                        ],
                    ],
                ],
                'borrower_id' => [
                    'type' => 'relation',
                    'query' => [
                        'slug' => 'borrowers',
                        'value' => 'id',
                        'text' => 'user.full_name',
                        'params' => [
                            'fields' => 'id,user.full_name',
                        ],
                    ],
                ],
            ],
            'filters' => $this->model::$filterTypes,
        ];

        $modelRules = $this->model->getRules('template', $request->user());
        foreach ($modelRules as $field => $rules) {
            $template['form'][$field]['rules'] = $this->formatRules($rules);
        }

        return $template;
    }
}
