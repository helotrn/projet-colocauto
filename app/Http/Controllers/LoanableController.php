<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\Loanable\DestroyRequest;
use App\Http\Requests\Bike\CreateRequest as BikeCreateRequest;
use App\Http\Requests\Bike\UpdateRequest as BikeUpdateRequest;
use App\Http\Requests\Car\CreateRequest as CarCreateRequest;
use App\Http\Requests\Car\UpdateRequest as CarUpdateRequest;
use App\Http\Requests\Trailer\CreateRequest as TrailerCreateRequest;
use App\Http\Requests\Trailer\UpdateRequest as TrailerUpdateRequest;
use App\Models\Bike;
use App\Models\Car;
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
        $validator = Validator::make(
            $request->all(),
            [
                'type' => 'in:bike,car,trailer',
            ]
        );

        if ($validator->fails()) {
            return $this->respondWithErrors($validator->errors());
        }

        switch ($request->get('type')) {
            case 'bike':
                $bikeRequest = $this->redirectRequest(BikeCreateRequest::class, $request);
                return $this->bikeController->create($bikeRequest);
                break;
            case 'car':
                $carRequest = $this->redirectRequest(CarCreateRequest::class, $request);
                return $this->carController->create($carRequest);
                break;
            case 'trailer':
                $trailerRequest = $this->redirectRequest(TrailerCreateRequest::class, $request);
                return $this->trailerController->create($trailerRequest);
                break;
            default:
                throw new \Exception('invalid loanable type');
        }
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
                return $this->bikeController->retrieve($request, $id);
            case 'car':
                return $this->carController->retrieve($request, $id);
            case 'trailer':
                return $this->trailerController->retrieve($request, $id);
            default:
                throw new \Exception('invalid loanable type');
        }
    }

    public function update(Request $request, $id) {
        $validator = Validator::make(
            $request->all(),
            [
                'type' => 'in:bike,car,trailer',
            ]
        );

        if ($validator->fails()) {
            return $this->respondWithErrors($validator->errors());
        }

        switch ($request->get('type')) {
            case 'bike':
                $bikeRequest = $this->redirectRequest(BikeUpdateRequest::class, $request);
                return $this->bikeController->update($bikeRequest, $id);
            case 'car':
                $carRequest = $this->redirectRequest(CarUpdateRequest::class, $request);
                return $this->carController->update($carRequest, $id);
            case 'trailer':
                $trailerRequest = $this->redirectRequest(TrailerUpdateRequest::class, $request);
                return $this->trailerController->update($trailerRequest, $id);
            default:
                throw new \Exception('invalid loanable type');
        }
    }

    public function destroy(DestroyRequest $request, $id) {
        $item = $this->repo->find($request, $id);

        switch ($item->type) {
            case 'bike':
                return $this->bikeController->destroy($request, $id);
            case 'car':
                return $this->carController->destroy($request, $id);
            case 'trailer':
                return $this->trailerController->destroy($request, $id);
            default:
                throw new \Exception('invalid loanable type');
        }
    }

    public function template(Request $request) {
        $template = [
            'item' => [
                'name' => '',
                'type' => null,
                'brand' => '',
                'comments' => '',
                'engine' => '',
                'instructions' => '',
                'insurer' => '',
                'location_description' => '',
                'model' => '',
                'name' => '',
                'ownership' => '',
                'papers_location' => '',
                'plate_number' => '',
                'position' => [],
                'transmission_mode' => '',
                'year_of_circulation' => '',
            ],
            'form' => [
                'general' => [
                    'name' => [
                        'type' => 'text',
                    ],
                    'position' => [
                        'type' => 'point',
                        'description' => "Cliquez sur la carte pour définir l'emplacement usuel"
                            . " du véhicule.",
                    ],
                    'location_description' => [
                        'type' => 'textarea',
                        'description' => 'Des indications qui pourraient aider les autres'
                         . ' membres de Locomotion à retrouver ce véhicule.',
                    ],
                    'comments' => [
                        'type' => 'textarea',
                    ],
                    'instructions' => [
                        'type' => 'textarea',
                        'description' => 'Y a-t-il des choses à savoir sur ce véhicule?',
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
                    'availability_json' => [
                        'type' => 'json',
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
                        'type' => 'select',
                        'options' => [
                            [
                                'text' => 'Dans la voiture',
                                'value' => 'in_the_car',
                            ],
                            [
                                'text' => 'À récupérer avec la voiture',
                                'value' => 'to_request_with_car',
                            ]
                        ],
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
                'trailer' => [
                    'maximum_charge' => [
                        'type' => 'text',
                    ],
                ],
            ],
            'filters' => $this->model::$filterTypes,
        ];

        $generalRules = $this->model->getRules();
        $generalRulesKeys = array_keys($generalRules);
        foreach ($generalRules as $field => $rules) {
            if (!isset($template['form']['general'][$field])) {
                continue;
            }
            $template['form']['general'][$field]['rules'] = $this->formatRules($rules);
        }

        $bikeRules = Bike::getRules();
        foreach ($bikeRules as $field => $rules) {
            if (in_array($field, $generalRulesKeys)) {
                continue;
            }
            if (!isset($template['form']['bike'][$field])) {
                continue;
            }
            $template['form']['bike'][$field]['rules'] = $this->formatRules($rules);
        }

        $carRules = Car::getRules();
        foreach ($carRules as $field => $rules) {
            if (in_array($field, $generalRulesKeys)) {
                continue;
            }
            if (!isset($template['form']['car'][$field])) {
                continue;
            }
            $template['form']['car'][$field]['rules'] = $this->formatRules($rules);
        }

        return $template;
    }
}
