<?php

namespace App\Http\Controllers;

use App\Events\LoanCreatedEvent;
use App\Events\Loan\CanceledEvent;
use App\Exports\LoansExport;
use App\Http\Requests\Action\ActionRequest;
use App\Http\Requests\Action\CreateRequest as ActionCreateRequest;
use App\Http\Requests\Loan\CreateRequest;
use App\Http\Requests\BaseRequest as Request;
use App\Models\Handover;
use App\Models\Intention;
use App\Models\Loan;
use App\Models\Loanable;
use App\Models\Payment;
use App\Models\PrePayment;
use App\Models\Takeover;
use App\Repositories\LoanRepository;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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

    public function create(CreateRequest $request)
    {
        if (!$request->get("community_id")) {
            $loanable = Loanable::accessibleBy($request->user())
                ->where("id", $request->get("loanable_id"))
                ->firstOrFail();
            $request->merge([
                "community_id" => $loanable->getCommunityForLoanBy(
                    $request->user()
                )->id,
            ]);
        }

        try {
            $item = parent::validateAndCreate($request);

            // Move loan forward if possible.
            self::loanActionsForward($item);
            // Refresh loan to get the newly created relationships
            $item->load("actions");
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        event(new LoanCreatedEvent($request->user(), $item));

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

    public function cancel(Request $request, $id)
    {
        $item = $this->repo->find($request, $id);

        $item->cancel();
        $item->save();

        try {
            $response = $this->respondWithItem($request, $item);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        event(new CanceledEvent($request->user(), $item));

        return $response;
    }

    public function retrieveBorrower(Request $request, $loanId)
    {
        $item = $this->repo->find($request, $loanId);

        return $this->respondWithItem($request, $item->borrower);
    }

    public function retrieveAction(Request $request, $loanId, $actionId)
    {
        $request->merge(["loan_id" => $loanId]);
        return $this->actionController->retrieve($request, $actionId);
    }

    public function createAction(ActionCreateRequest $request, $id)
    {
        $item = $this->repo->find($request->redirectAuth(Request::class), $id);

        $request->merge(["loan_id" => $id]);

        return $this->actionController->create($request);
    }

    public function template(Request $request)
    {
        $defaultDeparture = new Carbon();
        $defaultDeparture->minute = floor($defaultDeparture->minute / 10) * 10;
        $defaultDeparture->second = 0;

        $template = [
            "item" => [
                "departure_at" => $defaultDeparture->format("Y-m-d H:i:s"),
                "duration_in_minutes" => 60,
                "estimated_distance" => 10,
                "estimated_price" => 0,
                "platform_tip" => 0,
                "message_for_owner" => "",
                "reason" => "",
                "incidents" => [],
                "actions" => [],
                "borrower_id" => null,
                "borrower" => null,
                "loanable_id" => null,
                "loanable" => null,
            ],
            "form" => [
                "departure_at" => [
                    "type" => "datetime",
                ],
                "duration_in_minutes" => [
                    "type" => "number",
                ],
                "estimated_distance" => [
                    "type" => "number",
                ],
                "estimated_insurance" => [
                    "type" => "number",
                ],
                "estimated_price" => [
                    "type" => "number",
                ],
                "platform_tip" => [
                    "type" => "number",
                ],
                "message_for_owner" => [
                    "type" => "textarea",
                ],
                "reason" => [
                    "type" => "textarea",
                ],
                "community_id" => [
                    "type" => "relation",
                    "query" => [
                        "slug" => "communities",
                        "value" => "id",
                        "text" => "name",
                        "params" => [
                            "fields" => "id,name",
                        ],
                    ],
                ],
                "loanable_id" => [
                    "type" => "relation",
                    "query" => [
                        "slug" => "loanables",
                        "value" => "id",
                        "text" => "name",
                        "params" => [
                            "fields" => "id,name",
                        ],
                    ],
                ],
                "borrower_id" => [
                    "type" => "relation",
                    "query" => [
                        "slug" => "borrowers",
                        "value" => "id",
                        "text" => "user.full_name",
                        "params" => [
                            "fields" => "id,user.full_name",
                        ],
                    ],
                ],
            ],
            "filters" => $this->model::$filterTypes,
        ];

        $modelRules = $this->model->getRules("template", $request->user());
        foreach ($modelRules as $field => $rules) {
            $template["form"][$field]["rules"] = $this->formatRules($rules);
        }

        return $template;
    }

    // function to test if a loanable is available for the requested loan
    // verifies if there is no loan at the same time of the current loan to accept
    public function isAvailable(Request $request, $loanId)
    {
        try {
            // verify if the loan or the loanable exists, if not then it throws a ModelNotFoundException
            $loanable = null;
            $loan = $this->repo->find($request, $loanId);

            if ($loan) {
                $loanable = Loanable::accessibleBy($request->user())->find(
                    $loan->loanable_id
                );
            }
            if (!$loanable) {
                throw new ModelNotFoundException();
            }
        } catch (ModelNotFoundException $e) {
            return $this->respondWithMessage("Not found", 404);
        }

        $loanStart = new Carbon($loan->departure_at);
        $durationInMinutes = $loan->duration_in_minutes;

        $loanableAvailability = $loanable->isAvailable(
            $loanStart,
            $durationInMinutes,
            [$loanId]
        );

        return response(
            [
                "isAvailable" => $loanableAvailability,
            ],
            200
        );
    }

    /*
       This method creates next loan action upon completion of an action.
       It also checks if any action may be autocompleted.
     */
    public static function loanActionsForward($loan)
    {
        $intention = $loan->intention;

        // Ensure intention exists.
        if (!$intention) {
            $intention = new Intention();

            // https://laravel.com/docs/9.x/eloquent-relationships#the-save-method
            $loan->intention()->save($intention);
        }

        // Ensure intention is still in process to not complete the action every time we call this function.
        if ($intention->status == "in_process") {
            if ($loan->loanable->is_self_service) {
                // Autocomplete intention if loanable is self service.
                $intention->complete()->save();
            } elseif (
                // Autocomplete intention in private communities.
                $loan->loanable->owner->user->approvedCommunities
                    ->where("type", "private")
                    ->pluck("id")
                    ->intersect(
                        $loan->borrower->user->approvedCommunities
                            ->where("type", "private")
                            ->pluck("id")
                    )
                    ->intersect([$loan->community_id])
                    ->isNotEmpty()
            ) {
                $intention->complete()->save();
            }
        }

        // Ensure pre-payment exists if intention is completed.
        $prePayment = null;
        if ($intention && $intention->isCompleted()) {
            $prePayment = $loan->prePayment;

            if (!$prePayment) {
                $prePayment = new PrePayment();
                $loan->prePayment()->save($prePayment);
            }
        }

        if (!$prePayment) {
            return $loan;
        }

        // Autocomplete pre-payment if balance is sufficient.
        if ("in_process" == $prePayment->status) {
            if ($loan->borrower) {
                $borrowerUser = $loan->borrower->user;

                if ($borrowerUser->balance >= $loan->total_estimated_cost) {
                    $prePayment->complete()->save();
                }
            }
        }

        // Ensure takeover exists if pre-payment is completed.
        $takeover = null;
        if ($prePayment && $prePayment->isCompleted()) {
            $takeover = $loan->takeover;

            if (!$takeover) {
                $takeover = new Takeover();
                $loan->takeover()->save($takeover);
            }
        }

        if (!$takeover) {
            return $loan;
        }

        // Ensure handover exists if takeover is completed.
        $handover = null;
        if ($takeover && $takeover->isCompleted()) {
            $handover = $loan->handover;

            if (!$handover) {
                $handover = new Handover();
                $loan->handover()->save($handover);
            }
        }

        if (!$handover) {
            return $loan;
        }

        // Ensure payment exists if handover is completed.
        $payment = null;
        if ($handover->isCompleted()) {
            $payment = $loan->payment;

            if (!$payment) {
                $payment = new Payment();
                $loan->payment()->save($payment);
            }
        }

        // We don't complete payment here (yet?) because we would have to
        // generate the invoice which is done in PaymentController for the
        // moment.

        return $loan;
    }

    public function dashboard(Request $request)
    {
        $fields = [
            "id",
            "departure_at",
            "duration_in_minutes",
            "status",
            "total_final_cost",
            "final_price",
            "final_purchases_amount",
            "intention.id",
            "borrower.id",
            "borrower.user.avatar.*",
            "borrower.user.full_name",
            "borrower.user.id",
            "loanable.id",
            "loanable.image.*",
            "loanable.name",
            "loanable.owner.id",
            "loanable.owner.user.avatar.*",
            "loanable.owner.user.full_name",
            "loanable.owner.user.id",
            "loanable.type",
        ];

        $accessibleLoans = Loan::accessibleBy($request->user());
        $now = CarbonImmutable::now();
        $aWeekAgo = $now->copy()->subtract(7, "days");

        $completedLoans = (clone $accessibleLoans)
            ->where("status", "completed")
            ->where("actual_return_at", ">", $aWeekAgo)
            ->orderBy("updated_at", "desc");

        $ongoingLoans = (clone $accessibleLoans)
            ->where("status", "in_process")
            ->orderBy("departure_at", "asc");
        $waitingLoans = (clone $ongoingLoans)->whereHas("intention", function (
            Builder $q
        ) {
            $q->where("status", "in_process");
        });
        $userId = $request->user()->id;
        $waitingLoansAsBorrower = (clone $waitingLoans)->whereHas(
            "borrower",
            function (Builder $q) use ($userId) {
                $q->where("user_id", $userId);
            }
        );
        $waitingLoansAsOwner = (clone $waitingLoans)->whereHas(
            "loanable.owner",
            function (Builder $q) use ($userId) {
                $q->where("user_id", $userId);
            }
        );
        $approvedLoans = (clone $ongoingLoans)->whereHas("intention", function (
            Builder $q
        ) {
            $q->where("status", "completed");
        });

        // Started loans that aren't contested
        $startedLoans = (clone $approvedLoans)
            ->whereHas("takeover", function (Builder $q) {
                $q->where("status", "completed");
            })
            ->where(function ($q) {
                $q->doesntHave("handover")->orWhereHas("handover", function (
                    Builder $q
                ) {
                    $q->where("status", "!=", "canceled");
                });
            });

        $contestedLoans = (clone $approvedLoans)
            ->whereHas("takeover", function (Builder $q) {
                $q->where("status", "canceled");
            })
            ->orWhereHas("handover", function (Builder $q) {
                $q->where("status", "canceled");
            });

        $approvedFutureLoans = (clone $approvedLoans)->where(function (
            Builder $q
        ) {
            $q->doesntHave("takeover")->orWhereHas("takeover", function (
                Builder $q
            ) {
                $q->where("status", "in_process");
            });
        });
        return response(
            [
                "started" => $this->getCollectionFields(
                    $startedLoans->get(),
                    $fields
                ),
                "contested" => $this->getCollectionFields(
                    $contestedLoans->get(),
                    $fields
                ),
                "waiting" => $this->getCollectionFields(
                    $waitingLoansAsBorrower->get(),
                    $fields
                ),
                "need_approval" => $this->getCollectionFields(
                    $waitingLoansAsOwner->get(),
                    $fields
                ),
                // TODO: add a limit on the number of future loans once all loans can be displayed
                // in the user profile.
                "future" => $this->getCollectionFields(
                    $approvedFutureLoans->get(),
                    $fields
                ),
                "completed" => $this->getCollectionFields(
                    $completedLoans->limit(3)->get(),
                    $fields
                ),
            ],
            200
        );
    }
}
