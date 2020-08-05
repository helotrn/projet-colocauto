<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\Loanable\DestroyRequest;
use App\Http\Requests\Loanable\TestRequest;
use App\Http\Requests\Loanable\RestoreRequest;
use App\Http\Requests\Bike\CreateRequest as BikeCreateRequest;
use App\Http\Requests\Bike\UpdateRequest as BikeUpdateRequest;
use App\Http\Requests\Car\CreateRequest as CarCreateRequest;
use App\Http\Requests\Car\UpdateRequest as CarUpdateRequest;
use App\Http\Requests\Trailer\CreateRequest as TrailerCreateRequest;
use App\Http\Requests\Trailer\UpdateRequest as TrailerUpdateRequest;
use App\Exports\LoanablesExport;
use App\Models\Community;
use App\Models\Bike;
use App\Models\Car;
use App\Models\Loan;
use App\Models\Loanable;
use App\Models\Pricing;
use App\Repositories\LoanRepository;
use App\Repositories\LoanableRepository;
use Carbon\Carbon;
use Excel;
use Illuminate\Validation\ValidationException;
use Validator;

class LoanableController extends RestController
{
    protected $bikeController;
    protected $carController;
    protected $trailerController;

    protected $loanRepository;
    protected $loanController;

    public function __construct(
        LoanableRepository $repository,
        Loanable $model,
        BikeController $bikeController,
        CarController $carController,
        TrailerController $trailerController,
        LoanRepository $loanRepository,
        LoanController $loanController
    ) {
        $this->repo = $repository;
        $this->model = $model;

        $this->bikeController = $bikeController;
        $this->carController = $carController;
        $this->trailerController = $trailerController;

        $this->loanRepo = $loanRepository;
        $this->loanController = $loanController;
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
                $bikeRequest = $request->redirect(BikeCreateRequest::class);
                return $this->bikeController->create($bikeRequest);
            case 'car':
                $carRequest = $request->redirect(CarCreateRequest::class);
                return $this->carController->create($carRequest);
            case 'trailer':
                $trailerRequest = $request->redirect(TrailerCreateRequest::class);
                return $this->trailerController->create($trailerRequest);
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

        switch ($request->headers->get('accept')) {
            case 'text/csv':
                $filename = $this->respondWithCsv($request, $items, $this->model);
                $base = app()->make('url')->to('/');
                return response($base . $filename, 201);
            default:
                return $this->respondWithCollection($request, $items, $total);
        }
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
                $bikeRequest = $request->redirect(BikeUpdateRequest::class);
                return $this->bikeController->update($bikeRequest, $id);
            case 'car':
                $carRequest = $request->redirect(CarUpdateRequest::class);
                return $this->carController->update($carRequest, $id);
            case 'trailer':
                $trailerRequest = $request->redirect(TrailerUpdateRequest::class);
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

    public function restore(RestoreRequest $request, $id) {
        $request->merge([ 'deleted_at' => '0001-01-01' ]);
        $item = $this->repo->find($request, $id);

        switch ($item->type) {
            case 'bike':
                return $this->bikeController->restore($request, $id);
            case 'car':
                return $this->carController->restore($request, $id);
            case 'trailer':
                return $this->trailerController->restore($request, $id);
            default:
                throw new \Exception('invalid loanable type');
        }
    }

    public function test(TestRequest $request, $id) {
        $findRequest = $request->redirectAuth(Request::class);
        $item = $this->repo->find($findRequest, $id);

        $objectResponse = $this->retrieve($findRequest, $id);

        $estimatedDistance = $request->get('estimated_distance');
        $departureAt = new Carbon($request->get('departure_at'));
        $durationInMinutes = $request->get('duration_in_minutes');

        $communityId = $request->get('community_id');
        $community = Community::accessibleBy($request->user())->find($communityId);
        $pricing = $community->getPricingFor($item);

        $loanableData = json_decode($objectResponse->getContent());

        $end = $departureAt->copy()->add($durationInMinutes, 'minutes');

        $response = $pricing ? $pricing->evaluateRule(
            $estimatedDistance,
            $durationInMinutes,
            $loanableData,
            (object) [
                'days' => Loan::getCalendarDays($departureAt, $end),
                'start' => Pricing::dateToDataObject($departureAt),
                'end' => Pricing::dateToDataObject($end),
            ]
        ) : 0;

        if (is_array($response)) {
            [$price, $insurance] = $response;
        } else {
            $price = $response;
            $insurance = 0;
        }

        return response([
          'available' => $item->isAvailable($departureAt, $durationInMinutes),
          'price' => $price,
          'insurance' => $insurance,
          'pricing' => $pricing ? $pricing->name : 'Gratuit'
        ], 200);
    }

    public function template(Request $request) {
        $template = [
            'item' => [
                'name' => '',
                'type' => null,
                'brand' => '',
                'comments' => '',
                'community' => null,
                'engine' => '',
                'instructions' => '',
                'insurer' => '',
                'location_description' => '',
                'model' => '',
                'name' => '',
                'papers_location' => '',
                'plate_number' => '',
                'position' => [],
                'share_with_parent_communities' => false,
                'transmission_mode' => '',
                'year_of_circulation' => '',
            ],
            'form' => [
                'general' => [
                    'name' => [
                        'type' => 'text',
                    ],
                    'image' => [
                        'type' => 'image',
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
                    'community_id' => [
                        'type' => 'relation',
                        'query' => [
                            'slug' => 'communities',
                            'value' => 'id',
                            'text' => 'name',
                            'params' => [
                                'fields' => 'id,name,parent.id,parent.name',
                            ],
                        ],
                    ],
                    'owner_id' => [
                        'type' => 'relation',
                        'query' => [
                            'slug' => 'owners',
                            'value' => 'id',
                            'text' => 'user.full_name',
                            'params' => [
                                'fields' => 'id,user.full_name,'
                                    . 'user.communities.id,user.communities.name,'
                                    . 'user.communities.parent.id,user.communities.parent.name',
                            ],
                        ],
                    ],
                    'share_with_parent_communities' => [
                        'type' => 'checkbox',
                    ],
                    'padlock_id' => [
                        'type' => 'relation',
                        'query' => [
                            'slug' => 'padlocks',
                            'value' => 'id',
                            'text' => 'name',
                            'params' => [
                                'fields' => 'id,name',
                                '!loanable' => '1',
                            ],
                        ],
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
                                'text' => 'Cargo',
                                'value' => 'cargo',
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
                    'pricing_category' => [
                        'type' => 'select',
                        'options' => [
                            [
                                'text' => 'Petit (compacte, sous-compacte)',
                                'value' => 'small',
                            ],
                            [
                                'text' => 'Grand (van, VUS, pick-up)',
                                'value' => 'large',
                            ],
                        ],
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
                    'report' => [
                        'type' => 'file',
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

        $user = $request->user();

        $generalRules = $this->model->getRules('template', $user);
        $generalRulesKeys = array_keys($generalRules);
        foreach ($generalRules as $field => $rules) {
            if (!isset($template['form']['general'][$field])) {
                continue;
            }
            $template['form']['general'][$field]['rules'] = $this->formatRules($rules);
        }

        $bikeRules = Bike::getRules('template', $user);
        foreach ($bikeRules as $field => $rules) {
            if (in_array($field, $generalRulesKeys)) {
                continue;
            }
            if (!isset($template['form']['bike'][$field])) {
                continue;
            }
            $template['form']['bike'][$field]['rules'] = $this->formatRules($rules);
        }

        $carRules = Car::getRules('template', $user);
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

    public function indexLoans(Request $request, $id) {
        $item = $this->repo->find($request->redirectAuth(), $id);

        return $this->loanController->index($request->redirect(Request::class));
    }

    // WARN This bypasses "accessibleBy" checks on loans. Make sure
    // that the transformers authorize the fields down the line.
    public function retrieveNextLoan(Request $request, $loanableId, $loanId) {
        $item = $this->repo->find($request, $loanableId);
        $loan = $this->loanRepo->find($request, $loanId);

        $nextLoan = $item->loans()
            ->where('id', '!=', $loanId)
            ->where('departure_at', '>=', new \DateTime)
            ->orderBy('departure_at', 'asc')
            ->first();

        if (!$nextLoan) {
            return abort(404);
        }

        return $this->respondWithItem($request, $nextLoan);
    }
}
