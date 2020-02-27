<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Loanable;
use App\Repositories\LoanableRepository;
use Illuminate\Validation\ValidationException;
use Validator;

class LoanableController extends RestController
{
    protected $bikeController;
    protected $carController;
    protected $trailerController;

    public function __construct(
        LoanableRepository $repository,
        Loanable $model,
        BikeController $bikeController,
        CarController $carController,
        TrailerController $trailerController
    ) {
        $this->repo = $repository;
        $this->model = $model;
        $this->bikeController = $bikeController;
        $this->carController = $carController;
        $this->trailerController = $trailerController;
    }

    public function create(Request $request) {

        try {
            $item = parent::validateAndCreate($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item, 201);
    }

    public function index(Request $request) {
        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithCollection($request, $items, $total);
    }

    public function retrieve(Request $request, $id) {
        $item = $this->repo->find($request, $id);

        switch ($item->type) {
            case 'bike':
                $bikeRequest = new Request();
                $bikeRequest->setMethod('GET');
                $bikeRequest->request->add($request->all());
                return $this->bikeController->retrieve($bikeRequest, $id);
            case 'car':
                $carRequest = new Request();
                $carRequest->setMethod('GET');
                $carRequest->request->add($request->all());
                return $this->carController->retrieve($carRequest, $id);
            case 'trailer':
                $trailerRequest = new Request();
                $trailerRequest->setMethod('GET');
                $trailerRequest->request->add($request->all());
                return $this->trailerController->retrieve($trailerRequest, $id);
            default:
                throw new \Exception('invalid loanable type');
        }
    }

    public function update(Request $request, $id) {
        $validator = Validator::make(
            $request->all(),
            [
                'rule' => 'one_of:bike,car,trailer',
            ]
        );

        if ($validator->fails()) {
            return $this->respondWithErrors($validator->errors());
        }

        switch ($request->get('type')) {
            case 'bike':
                $bikeRequest = new Request();
                $bikeRequest->setMethod('POST');
                $bikeRequest->request->add($request->all());
                return $this->bikeController->update($bikeRequest, $id);
            case 'car':
                $carRequest = new Request();
                $carRequest->setMethod('POST');
                $carRequest->request->add($request->all());
                return $this->carController->update($carRequest, $id);
            case 'trailer':
                $trailerRequest = new Request();
                $trailerRequest->setMethod('POST');
                $trailerRequest->request->add($request->all());
                return $this->trailerController->update($trailerRequest, $id);
            default:
                throw new \Exception('invalid loanable type');
        }
    }

    public function template(Request $request) {
        $template = [
            'item' => [
                'name' => '',
                'type' => null,
                'location_description' => '',
                'instructions' => '',
                'comments' => '',
                'availability_ics' => '',
            ],
            'form' => [
                'general' => [
                    'name' => [
                        'type' => 'text',
                    ],
                    'position' => [
                        'type' => 'point',
                    ],
                    'location_description' => [
                        'type' => 'textarea',
                    ],
                    'comments' => [
                        'type' => 'textarea',
                    ],
                    'instructions' => [
                        'type' => 'textarea',
                    ],
                    'type' => [
                        'type' => 'select',
                        'options' => [
                            [
                                'text' => 'Voiture',
                                'value' => 'car',
                            ],
                            [
                                'text' => 'Vélo',
                                'value' => 'bike',
                            ],
                            [
                                'text' => 'Remorque',
                                'value' => 'trailer',
                            ],
                        ],
                    ],
                    'availability_ics' => [
                        'type' => 'text',
                    ],
                    'owner_id' => [
                        'type' => 'relation',
                    ],
                    'community_id' => [
                        'type' => 'relation',
                    ],
                ],
                'bike' => [
                    'model' => [
                        'type' => 'text',
                    ],
                    'bike_type' => [
                        'type' => 'select',
                        'options' => [
                            [
                                'text' => 'Régulier',
                                'value' => 'regular',
                            ],
                            [
                                'text' => 'Électrique',
                                'value' => 'electric',
                            ],
                            [
                                'text' => 'Roue fixe',
                                'value' => 'fixed_wheel',
                            ],
                        ],
                    ],
                    'size' => [
                        'type' => 'select',
                        'options' => [
                            [
                                'text' => 'Grand',
                                'value' => 'big',
                            ],
                            [
                                'text' => 'Moyen',
                                'value' => 'medium',
                            ],
                            [
                                'text' => 'Petit',
                                'value' => 'small',
                            ],
                            [
                                'text' => 'Enfant',
                                'value' => 'kid',
                            ],
                        ],
                    ],
                ],
                'car' => [
                    'brand' => [
                        'type' => 'text',
                    ],

                    'model' => [
                        'type' => 'text',
                    ],
                    'year_of_circulation' => [
                        'type' => 'number',
                    ],
                    'transmission_mode' => [
                        'type' => 'select',
                        'options' => [
                            [
                                'text' => 'Automatique',
                                'value' => 'automatic',
                            ],
                            [
                                'text' => 'Manuelle',
                                'value' => 'manual',
                            ],
                        ],
                    ],
                    'engine' => [
                        'type' => 'select',
                        'options' => [
                            [
                                'text' => 'Essence',
                                'value' => 'fuel',
                            ],
                            [
                                'text' => 'Diesel',
                                'value' => 'diesel',
                            ],
                            [
                                'text' => 'Électrique',
                                'value' => 'electric',
                            ],
                            [
                                'text' => 'Hybride',
                                'value' => 'hybrid',
                            ],
                        ],
                    ],
                    'plate_number' => [
                        'type' => 'text',
                    ],
                    'is_value_over_fifty_thousand' => [
                        'type' => 'checkbox',
                    ],
                    'ownership' => [
                        'type' => 'select',
                        'options' => [
                            [
                                'text' => 'Propriétaire',
                                'value' => 'self',
                            ],
                            [
                                'text' => 'Location',
                                'value' => 'rental',
                            ]
                        ],
                    ],
                    'papers_location' => [
                        'type' => 'text',
                    ],
                    'has_accident_report' => [
                        'type' => 'checkbox',
                    ],
                    'insurer' => [
                        'type' => 'text',
                    ],
                    'has_informed_insurer' => [
                        'type' => 'checkbox',
                    ],
                ],
                'trailer' => [],
            ],
            'filters' => $this->model::$filterTypes,
        ];

        $rules = $this->model->getRules();
        foreach (['name', 'position', 'type'] as $field) {
            $template['form']['general'][$field]['rules'] = $this->formatRules($rules[$field]);
        }

        $bikeRules = Bike::getRules();
        foreach (['model', 'bike_type', 'size'] as $field) {
            $template['form']['bike'][$field]['rules'] = $this->formatRules($bikeRules[$field]);
        }

        return $template;
    }
}
