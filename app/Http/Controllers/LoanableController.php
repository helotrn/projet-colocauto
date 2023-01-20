<?php

namespace App\Http\Controllers;

use App\Calendar\AvailabilityHelper;
use App\Calendar\DateIntervalHelper;
use App\Helpers\Order as OrderHelper;
use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\Bike\CreateRequest as BikeCreateRequest;
use App\Http\Requests\Bike\UpdateRequest as BikeUpdateRequest;
use App\Http\Requests\Car\CreateRequest as CarCreateRequest;
use App\Http\Requests\Car\UpdateRequest as CarUpdateRequest;
use App\Http\Requests\Loanable\AddCoownerRequest;
use App\Http\Requests\Loanable\AvailabilityRequest;
use App\Http\Requests\Loanable\DestroyRequest;
use App\Http\Requests\Loanable\EventsRequest;
use App\Http\Requests\Loanable\RemoveCoownerRequest;
use App\Http\Requests\Loanable\RestoreRequest;
use App\Http\Requests\Loanable\SearchRequest;
use App\Http\Requests\Loanable\TestRequest;
use App\Http\Requests\Trailer\CreateRequest as TrailerCreateRequest;
use App\Http\Requests\Trailer\UpdateRequest as TrailerUpdateRequest;
use App\Models\Bike;
use App\Models\Car;
use App\Models\Community;
use App\Models\Loan;
use App\Models\Loanable;
use App\Models\Pricing;
use App\Models\Trailer;
use App\Repositories\LoanableRepository;
use App\Repositories\LoanRepository;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
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

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "type" => "in:bike,car,trailer",
        ]);

        if ($validator->fails()) {
            return $this->respondWithErrors($validator->errors());
        }

        switch ($request->get("type")) {
            case "bike":
                $bikeRequest = $request->redirect(BikeCreateRequest::class);
                return $this->bikeController->create($bikeRequest);
            case "car":
                $carRequest = $request->redirect(CarCreateRequest::class);
                return $this->carController->create($carRequest);
            case "trailer":
                $trailerRequest = $request->redirect(
                    TrailerCreateRequest::class
                );
                return $this->trailerController->create($trailerRequest);
            default:
                throw new \Exception("invalid loanable type");
        }
    }

    public function availability(AvailabilityRequest $request)
    {
        // Set time to 0 to ensure consistency with the fact that we expect dates.
        $dateRange = [
            (new CarbonImmutable($request->start))->setTime(0, 0, 0),
            (new CarbonImmutable($request->end))->setTime(0, 0, 0),
        ];

        // AvailabilityRequest checks that the loanable exists and is accessible by the user.
        $loanable = Loanable::findOrFail($request->loanable_id);

        $events = [];

        $availabilityRules = $loanable->getAvailabilityRules();
        $availabilityMode = $loanable->availability_mode;

        // Availability or unavailability depending on responseMode.
        $availabilityIntervalsByDay = AvailabilityHelper::getDailyAvailability(
            [
                "available" => "always" == $availabilityMode,
                "rules" => $availabilityRules,
            ],
            $dateRange,
            $request->responseMode == "available"
        );

        $loans = Loan::accessibleBy($request->user())
            ->where("loanable_id", "=", $loanable->id)
            ->isPeriodUnavailable($dateRange[0], $dateRange[1])
            ->get();

        foreach ($loans as $loan) {
            $loanInterval = [
                new Carbon($loan->departure_at),
                new Carbon($loan->actual_return_at),
            ];

            $loanIntervalsByDay = AvailabilityHelper::splitIntervalByDay(
                $loanInterval
            );

            foreach ($loanIntervalsByDay as $index => $loanInterval) {
                if( $request->responseMode == "loans" ) {
                    $events[] = [
                        "start" => $loanInterval[0]->toDateTimeString(),
                        "end" => $loanInterval[1]->toDateTimeString(),
                        "data" => [
                            "available" => $request->responseMode == "available",
                        ],
                        "type" => "availability",
                        "title" => $loan->borrower->user->full_name .' - '. $loan->reason,
                    ];
                } else {
                
                $availabilityIntervals = isset(
                    $availabilityIntervalsByDay[$index]
                )
                    ? $availabilityIntervalsByDay[$index]
                    : [];

                if ($request->responseMode == "available") {
                    // Intervals are of availability.
                    $availabilityIntervalsByDay[
                        $index
                    ] = DateIntervalHelper::subtraction(
                        $availabilityIntervals,
                        $loanInterval
                    );
                } else {
                    // Intervals are of unavailability.
                    $availabilityIntervalsByDay[
                        $index
                    ] = DateIntervalHelper::union(
                        $availabilityIntervals,
                        $loanInterval
                    );
                }
                }
            }
        }

        if( $request->responseMode != "loans" ) {
        // Generate events from intervals.
        foreach ($availabilityIntervalsByDay as $dailyAvailabilityIntervals) {
            foreach ($dailyAvailabilityIntervals as $availabilityInterval) {
                $events[] = [
                    "start" => $availabilityInterval[0]->toDateTimeString(),
                    "end" => $availabilityInterval[1]->toDateTimeString(),
                    "data" => [
                        "available" => $request->responseMode == "available",
                    ],
                    "type" => "availability",
                ];
            }
        }
        }

        return response($events, 200);
    }

    public function events(EventsRequest $request)
    {
        $start = new Carbon($request->start);
        $end = new Carbon($request->end);

        $loanable = Loanable::accessibleBy($request->user())
            ->where("id", "=", $request->loanable_id)
            ->first();

        $events = [];

        if ($loanable) {
            $availabilityRules = $loanable->getAvailabilityRules();
            $availabilityMode = $loanable->availability_mode;

            $intervals = AvailabilityHelper::getScheduleDailyIntervals(
                [
                    "available" => "always" == $availabilityMode,
                    "rules" => $availabilityRules,
                ],
                [$start, $end]
            );

            foreach ($intervals as $interval) {
                $events[] = [
                    "type" => "availability_rule",
                    "start" => $interval[0],
                    "end" => $interval[1],
                    "uri" => "/loanables/$loanable->id",
                    "data" => [
                        // availability_mode == "always" means that events are of unavailability.
                        "available" => $availabilityMode != "always",
                    ],
                ];
            }

            $loans = Loan::accessibleBy($request->user())
                ->where("loanable_id", "=", $loanable->id)
                // Departure before the end and return after the beginning of the period.
                ->where("departure_at", "<", $end)
                ->where("actual_return_at", ">", $start)
                ->get();

            foreach ($loans as $loan) {
                $events[] = [
                    "type" => "loan",
                    "start" => new Carbon($loan->departure_at),
                    "end" => new Carbon($loan->actual_return_at),
                    "uri" => "/loans/$loan->id",
                    "data" => [
                        "status" => $loan->status,
                    ],
                ];
            }

            // Event field definitions for sorting.
            $eventFieldDefs = [
                "type" => ["type" => "string"],
                "uri" => ["type" => "string"],
                "start" => ["type" => "carbon"],
                "end" => ["type" => "carbon"],
            ];

            $orderArray = OrderHelper::parseOrderRequestParam(
                $request->order,
                $eventFieldDefs
            );
            $events = OrderHelper::sortArray($events, $orderArray);

            // Prepare data for response.
            foreach ($events as $key => $event) {
                $events[$key]["start"] = $event["start"]->toDateTimeString();
                $events[$key]["end"] = $event["end"]->toDateTimeString();
            }
        }

        return response($events, 200);
    }

    public function index(Request $request)
    {
        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        switch ($request->headers->get("accept")) {
            case "text/csv":
                $filename = $this->respondWithCsv(
                    $request,
                    $items,
                    $this->model
                );
                return response(
                    env("BACKEND_URL_FROM_BROWSER") . $filename,
                    201
                );
            default:
                return $this->respondWithCollection($request, $items, $total);
        }
    }

    public function retrieve(Request $request, $id)
    {
        $item = $this->repo->find($request, $id);

        switch ($item->type) {
            case "bike":
                return $this->bikeController->retrieve($request, $id);
            case "car":
                return $this->carController->retrieve($request, $id);
            case "trailer":
                return $this->trailerController->retrieve($request, $id);
            default:
                throw new \Exception("invalid loanable type");
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "type" => "in:bike,car,trailer",
        ]);

        if ($validator->fails()) {
            return $this->respondWithErrors($validator->errors());
        }

        switch ($request->get("type")) {
            case "bike":
                $bikeRequest = $request->redirect(BikeUpdateRequest::class);
                return $this->bikeController->update($bikeRequest, $id);
            case "car":
                $carRequest = $request->redirect(CarUpdateRequest::class);
                return $this->carController->update($carRequest, $id);
            case "trailer":
                $trailerRequest = $request->redirect(
                    TrailerUpdateRequest::class
                );
                return $this->trailerController->update($trailerRequest, $id);
            default:
                throw new \Exception("invalid loanable type");
        }
    }

    public function destroy(DestroyRequest $request, $id)
    {
        $item = $this->repo->find($request, $id);

        switch ($item->type) {
            case "bike":
                return $this->bikeController->destroy($request, $id);
            case "car":
                return $this->carController->destroy($request, $id);
            case "trailer":
                return $this->trailerController->destroy($request, $id);
            default:
                throw new \Exception("invalid loanable type");
        }
    }

    public function restore(RestoreRequest $request, $id)
    {
        $item = $this->repo->findWithTrashed($request, $id);

        switch ($item->type) {
            case "bike":
                return $this->bikeController->restore($request, $id);
            case "car":
                return $this->carController->restore($request, $id);
            case "trailer":
                return $this->trailerController->restore($request, $id);
            default:
                throw new \Exception("invalid loanable type");
        }
    }

    public function list(Request $request)
    {
        $possibleTypes = ["bike", "car", "trailer"];
        $validator = Validator::make($request->all(), [
            "types" => [
                "string",
                function ($attribute, $value, $fail) use ($possibleTypes) {
                    $typesToValidate = explode(",", $value);
                    foreach ($typesToValidate as $type) {
                        if (!in_array($type, $possibleTypes)) {
                            $fail(
                                trans("validation.custom.loanable_types", [
                                    "givenValues" => $value,
                                    "validValues" => implode(
                                        ",",
                                        $possibleTypes
                                    ),
                                ])
                            );
                        }
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return $this->respondWithErrors($validator->errors());
        }

        // Default to all types
        $types = $possibleTypes;
        if ($request->has("types")) {
            $types = explode(",", $request->query("types"));
        }

        $output = [];
        $loanableFields = [
            "id",
            "comments",
            "is_self_service",
            "location_description",
            "name",
            "owner.user.id",
            "owner.user.avatar",
            "owner.user.name",
            "owner.user.last_name",
            "owner.user.full_name",
            "type",
            "image.*",
        ];

        // Possibly cache these per community per type.

        if (in_array("car", $types)) {
            $carFields = array_merge($loanableFields, [
                "brand",
                "engine",
                "transmission_mode",
                "year_of_circulation",
                "papers_location",
                "model",
            ]);
            $output["cars"] = $this->getCollectionFields(
                Car::accessibleBy($request->user())
                    ->hasAvailabilities()
                    ->get(),
                $carFields
            );
        }

        if (in_array("bike", $types)) {
            $bikeFields = array_merge($loanableFields, [
                "bike_type",
                "model",
                "size",
            ]);
            $output["bikes"] = $this->getCollectionFields(
                Bike::accessibleBy($request->user())
                    ->hasAvailabilities()
                    ->get(),
                $bikeFields
            );
        }

        if (in_array("trailer", $types)) {
            $trailerFields = array_merge($loanableFields, [
                "maximum_charge",
                "dimensions",
            ]);
            $output["trailers"] = $this->getCollectionFields(
                Trailer::accessibleBy($request->user())
                    ->hasAvailabilities()
                    ->get(),
                $trailerFields
            );
        }

        return response($output, 200);
    }

    public function search(SearchRequest $request)
    {
        $departureAt = new Carbon($request->get("departure_at"));
        $durationInMinutes = $request->get("duration_in_minutes");

        $returnAt = $departureAt->copy()->add($durationInMinutes, "minutes");

        $loanables = Loanable::accessibleBy($request->user());

        // Check no other loans intersect
        $availableLoanables = $loanables
            ->whereDoesntHave("loans", function ($loans) use (
                $departureAt,
                $returnAt
            ) {
                return $loans->isPeriodUnavailable($departureAt, $returnAt);
            })
            ->get();

        // Check schedule is open for remaining loanables
        $availableLoanables = $availableLoanables->reject(function (
            $loanable
        ) use ($departureAt, $returnAt) {
            return !$loanable->isLoanableScheduleOpen($departureAt, $returnAt);
        });

        // Estimate price for each available loanable
        $estimatedDistance = $request->get("estimated_distance");

        $loanablesAndCosts = $availableLoanables->map(function (
            Loanable $loanable
        ) use ($request, $estimatedDistance, $durationInMinutes, $departureAt) {
            $community = $loanable->getCommunityForLoanBy($request->user());
            return (object) [
                "loanableId" => $loanable->id,
                "estimatedCost" => self::estimateLoanCost(
                    $loanable,
                    $community,
                    $estimatedDistance,
                    $durationInMinutes,
                    $departureAt
                ),
            ];
        });

        return response($loanablesAndCosts, 200);
    }

    /**
     * Returns the estimated price a possible loan
     *
     * @return object with 'price', 'insurance' and 'pricing' (name of the pricing rule) fields set.
     **/
    private function estimateLoanCost(
        Loanable $loanable,
        Community $community,
        int $estimatedDistance,
        int $durationInMinutes,
        Carbon $departureAt
    ): object {
        $pricing = $community->getPricingFor($loanable);
        if (!$pricing) {
            return (object) [
                "price" => 0,
                "insurance" => 0,
                "pricing" => "Gratuit",
            ];
        }

        $end = $departureAt->copy()->add($durationInMinutes, "minutes");
        $estimatedCost = $pricing->evaluateRule(
            $estimatedDistance,
            $durationInMinutes,
            // This lets us access car specific fields, i.e. pricing_category
            self::getSpecificLoanable($loanable)->toArray(),
            (object) [
                "days" => Loan::getCalendarDays($departureAt, $end),
                "start" => Pricing::dateToDataObject($departureAt),
                "end" => Pricing::dateToDataObject($end),
            ]
        );

        if (is_array($estimatedCost)) {
            [$price, $insurance] = $estimatedCost;
        } else {
            $price = $estimatedCost;
            $insurance = 0;
        }

        return (object) [
            "price" => $price,
            "insurance" => $insurance,
            "pricing" => $pricing->name,
        ];
    }

    private function getSpecificLoanable($loanable)
    {
        switch ($loanable->type) {
            case "bike":
                return Bike::find($loanable->id);
            case "car":
                return Car::find($loanable->id);
            case "trailer":
                return Trailer::find($loanable->id);
            default:
                throw new \Exception("invalid loanable type");
        }
    }

    public function test(TestRequest $request, $id)
    {
        $findRequest = $request->redirectAuth(Request::class);
        $item = $this->repo->find($findRequest, $id);

        $estimatedDistance = $request->get("estimated_distance");
        $departureAt = new Carbon($request->get("departure_at"));
        $durationInMinutes = $request->get("duration_in_minutes");

        $communityId = $request->get("community_id");
        if ($communityId) {
            $community = Community::accessibleBy($request->user())->find(
                $communityId
            );
        } else {
            $community = $item->getCommunityForLoanBy($request->user());
        }
        $estimatedCost = self::estimateLoanCost(
            $item,
            $community,
            $estimatedDistance,
            $durationInMinutes,
            $departureAt
        );

        return response(
            [
                "community" => [
                    "id" => $community->id,
                    "name" => $community->name,
                ],
                "available" => $item->isAvailable(
                    $departureAt,
                    $durationInMinutes
                ),
                "price" => $estimatedCost->price,
                "insurance" => $estimatedCost->insurance,
                "pricing" => $estimatedCost->pricing,
            ],
            200
        );
    }

    public function template(Request $request)
    {
        $template = [
            "item" => [
                "type" => "car",
                "brand" => "",
                "comments" => "",
                "community" => null,
                "engine" => "",
                "instructions" => "",
                "insurer" => "",
                "location_description" => "",
                "model" => "",
                "name" => "",
                "papers_location" => "",
                "plate_number" => "",
                "share_with_parent_communities" => false,
                "transmission_mode" => "",
                "year_of_circulation" => null,
                "cost_per_km" => 0.70,
                "cost_per_month" => 30,
                "owner_compensation" => 30,
            ],
            "form" => [
                "general" => [
                    "name" => [
                        "type" => "text",
                    ],
                    "image" => [
                        "type" => "image",
                    ],
                    "location_description" => [
                        "type" => "textarea",
                    ],
                    "comments" => [
                        "type" => "textarea",
                    ],
                    "instructions" => [
                        "type" => "textarea",
                    ],
                    "type" => [
                        "type" => "select",
                        "options" => [
                            [
                                "text" => "Auto",
                                "value" => "car",
                            ],
                        ],
                        "disabled" => true,
                        "hidden" => true,
                    ],
                    "community_id" => [
                        "type" => "relation",
                        "query" => [
                            "slug" => "communities",
                            "value" => "id",
                            "text" => "name",
                            "params" => [
                                "fields" => "id,name,parent.id,parent.name",
                            ],
                        ],
                    ],
                    "owner_id" => [
                        "type" => "relation",
                        "query" => [
                            "slug" => "owners",
                            "value" => "id",
                            "text" => "user.full_name",
                            "params" => [
                                "fields" =>
                                    "id,user.full_name," .
                                    "user.communities.id,user.communities.name," .
                                    "user.communities.parent.id,user.communities.parent.name",
                            ],
                        ],
                    ],
                    "share_with_parent_communities" => [
                        "type" => "checkbox",
                    ],
                    "is_self_service" => [
                        "type" => "checkbox",
                    ],
                    "padlock_id" => [
                        "type" => "relation",
                        "query" => [
                            "slug" => "padlocks",
                            "value" => "id",
                            "text" => "name",
                            "params" => [
                                "fields" => "id,name",
                                "!loanable" => "1",
                            ],
                        ],
                    ],
                ],
                "bike" => [
                    "model" => [
                        "type" => "text",
                    ],
                    "bike_type" => [
                        "type" => "select",
                        "options" => [
                            [
                                "text" => "Régulier",
                                "value" => "regular",
                            ],
                            [
                                "text" => "Cargo",
                                "value" => "cargo",
                            ],
                            [
                                "text" => "Électrique",
                                "value" => "electric",
                            ],
                            [
                                "text" => "Roue fixe",
                                "value" => "fixed_wheel",
                            ],
                        ],
                    ],
                    "size" => [
                        "type" => "select",
                        "options" => [
                            [
                                "text" => "Grand",
                                "value" => "big",
                            ],
                            [
                                "text" => "Moyen",
                                "value" => "medium",
                            ],
                            [
                                "text" => "Petit",
                                "value" => "small",
                            ],
                            [
                                "text" => "Enfant",
                                "value" => "kid",
                            ],
                        ],
                    ],
                ],
                "car" => [
                    "brand" => [
                        "type" => "text",
                    ],
                    "model" => [
                        "type" => "text",
                    ],
                    "pricing_category" => [
                        "type" => "select",
                        "options" => [
                            [
                                "text" =>
                                    "Petite auto (compacte, sous-compacte, hybride non-branchable)",
                                "value" => "small",
                            ],
                            [
                                "text" => "Grosse auto (van, VUS, pick-up)",
                                "value" => "large",
                            ],
                            [
                                "text" =>
                                    "Auto électrique (électrique, hybride branchable)",
                                "value" => "electric",
                            ],
                        ],
                        "hidden" => true,
                    ],
                    "year_of_circulation" => [
                        "type" => "number",
                        "max" => (int) date("Y") + 1,
                        "min" => 1900,
                    ],
                    "transmission_mode" => [
                        "type" => "select",
                        "options" => [
                            [
                                "text" => "Automatique",
                                "value" => "automatic",
                            ],
                            [
                                "text" => "Manuelle",
                                "value" => "manual",
                            ],
                        ],
                    ],
                    "engine" => [
                        "type" => "select",
                        "options" => [
                            [
                                "text" => "Essence",
                                "value" => "fuel",
                            ],
                            [
                                "text" => "Diesel",
                                "value" => "diesel",
                            ],
                            [
                                "text" => "Électrique",
                                "value" => "electric",
                            ],
                            [
                                "text" => "Hybride",
                                "value" => "hybrid",
                            ],
                        ],
                    ],
                    "plate_number" => [
                        "type" => "text",
                    ],
                    "is_value_over_fifty_thousand" => [
                        "type" => "checkbox",
                    ],
                    "report" => [
                        "type" => "file",
                    ],
                    "papers_location" => [
                        "type" => "select",
                        "options" => [
                            [
                                "text" => "Dans la voiture",
                                "value" => "in_the_car",
                            ],
                            [
                                "text" => "À récupérer avec la voiture",
                                "value" => "to_request_with_car",
                            ],
                        ],
                    ],
                    "insurer" => [
                        "type" => "text",
                    ],
                ],
                "trailer" => [
                    "maximum_charge" => [
                        "type" => "text",
                    ],
                    "dimensions" => [
                        "type" => "text",
                    ],
                ],
                "costs" => [
                    "cost_per_km" => [
                        "type" => "currency",
                    ],
                    "cost_per_month" => [
                        "type" => "currency",
                    ],
                    "owner_compensation" => [
                        "type" => "currency",
                    ],
                ]
            ],
            "filters" => $this->model::$filterTypes,
        ];

        $user = $request->user();

        $generalRules = $this->model->getRules("template", $user);
        $generalRulesKeys = array_keys($generalRules);
        foreach ($generalRules as $field => $rules) {
            if (!isset($template["form"]["general"][$field])) {
                continue;
            }
            $template["form"]["general"][$field]["rules"] = $this->formatRules(
                $rules
            );
        }

        $bikeRules = Bike::getRules("template", $user);
        foreach ($bikeRules as $field => $rules) {
            if (in_array($field, $generalRulesKeys)) {
                continue;
            }
            if (!isset($template["form"]["bike"][$field])) {
                continue;
            }
            $template["form"]["bike"][$field]["rules"] = $this->formatRules(
                $rules
            );
        }

        $carRules = Car::getRules("template", $user);
        foreach ($carRules as $field => $rules) {
            if (in_array($field, $generalRulesKeys)) {
                continue;
            }
            if (!isset($template["form"]["car"][$field])) {
                continue;
            }
            $template["form"]["car"][$field]["rules"] = $this->formatRules(
                $rules
            );
        }

        return $template;
    }

    public function indexLoans(Request $request, $id)
    {
        $this->repo->find($request->redirectAuth(), $id);

        return $this->loanController->index($request->redirect(Request::class));
    }

    // WARN This bypasses "accessibleBy" checks on loans. Make sure
    // that the transformers authorize the fields down the line.
    public function retrieveNextLoan(Request $request, $loanableId, $loanId)
    {
        $item = $this->repo->find($request, $loanableId);
        $loan = $this->loanRepo->find($request, $loanId);

        $loanReturnAt = (new Carbon($loan->departure_at))->add(
            $loan->duration_in_minutes,
            "minutes"
        );
        $nextLoan = $item
            ->loans()
            ->where("loans.id", "!=", $loanId)
            ->where("departure_at", ">=", $loanReturnAt)
            ->orderBy("departure_at", "asc")
            ->first();

        if (!$nextLoan) {
            return abort(404);
        }

        return $this->respondWithItem($request, $nextLoan);
    }

    public function addCoowner(AddCoownerRequest $request, $loanableId)
    {
        $loanable = Loanable::findOrFail($loanableId);
        $loanable->addCoowner($request->get("user_id"));
    }

    public function removeCoowner(RemoveCoownerRequest $request, $loanableId)
    {
        $loanable = Loanable::findOrFail($loanableId);
        $loanable->removeCoowner($request->get("user_id"));
    }
}
