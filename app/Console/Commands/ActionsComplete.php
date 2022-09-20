<?php

namespace App\Console\Commands;

use App\Http\Controllers\ActionController;
use App\Http\Requests\Action\ActionRequest;
use App\Models\Action;
use App\Models\Loan;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Log;

class ActionsComplete extends Command
{
    protected $signature = "actions:complete";

    protected $description = "Complete actions after 48 hours of inactivity";

    private $controller;

    public function __construct(ActionController $controller)
    {
        parent::__construct();

        $this->controller = $controller;
    }

    public function handle()
    {
        Log::info("Starting actions autocompletion command...");

        /*
           Strategy:

           1. Quickly find loans (getActiveLoansScheduledToReturnBefore):
               - that are active;
               - that were registered to be finished by 48 hours ago
                 (departure_at + duration_in_minutes) in table "loans".

           2. Then refine calculation of the real return_at time accounting for
              extensions.

           3. Decide whether to cancel, complete or leave the loan intact.

           NOTE: Early payments are not expected as it would indicate that the
           loan is already completed.

           This implementation creates requests and uses the HTTP Controller
           because it contains some of the business logic that must be
           applied and some events that should be triggered.
           See PaymentController::complete for example.
        */

        $loanExpirationTime = CarbonImmutable::now()->subHours(48);

        $loans = self::getActiveLoansScheduledToReturnBefore(
            $loanExpirationTime
        );

        foreach ($loans as $loan) {
            // Calculate actual return considering extentions
            $actualEnd = (new Carbon($loan->departure_at))->addMinutes(
                $loan->actual_duration_in_minutes
            );

            if ($actualEnd->greaterThanOrEqualTo($loanExpirationTime)) {
                Log::info(
                    "Not autocompleting loan ID $loan->id because of an accepted extension..."
                );
                continue;
            }

            /*
              Extensions:

              Cancel extensions if they were not accepted before the previously accepted
              loan duration (this means accounting for extensions accepted earlier).
            */
            foreach ($loan->actions as $action) {
                if (
                    "extension" == $action->type &&
                    "in_process" == $action->status
                ) {
                    $actualReturnAt = Carbon::parse(
                        $loan->departure_at
                    )->addMinutes($loan->actual_duration_in_minutes);

                    if (
                        $actualReturnAt->lessThanOrEqualTo($loanExpirationTime)
                    ) {
                        Log::info(
                            "Canceling $action->type on loan ID $loan->id..."
                        );

                        $request = new ActionRequest();
                        $request->merge([
                            "type" => $action->type,
                            "loan_id" => $loan->id,
                            "new_duration" => $action->new_duration,
                        ]);

                        $this->controller->cancel(
                            $request,
                            $loan->id,
                            $action->id
                        );

                        Log::info(
                            "Canceled $action->type on loan ID $loan->id."
                        );
                    }
                }
            }

            // Canceled extensions will not change loan status. Not necessary to refresh loan.

            /*
              Intentions:

              Cancel loan if intention is in process
            */
            foreach ($loan->actions as $action) {
                if (
                    "intention" == $action->type &&
                    "in_process" == $action->status
                ) {
                    Log::info(
                        "Autocancelling loan ID $loan->id on $action->type action..."
                    );
                    $loan->cancel()->save();
                    Log::info("Canceled loan ID $loan->id.");
                }
            }

            /*
              Prepayments:

              Cancel loan if prepayment is in process
            */
            foreach ($loan->actions as $action) {
                if (
                    "pre_payment" == $action->type &&
                    "in_process" == $action->status
                ) {
                    Log::info(
                        "Autocancelling loan ID $loan->id on $action->type action..."
                    );
                    $loan->cancel()->save();
                    Log::info("Canceled loan ID $loan->id.");
                }
            }

            /*
              Takeovers:

              Complete takeover if loan is selfservice
              Cancel loan on takeover if it is not selfservice
            */
            foreach ($loan->actions as $action) {
                if (
                    "takeover" == $action->type &&
                    "in_process" == $action->status
                ) {
                    if ($loan->loanable->is_self_service) {
                        Log::info(
                            "Autocompleting $action->type on loan ID $loan->id..."
                        );

                        $request = new ActionRequest();
                        $request->setUserResolver(function () use ($loan) {
                            return $loan->borrower->user;
                        });
                        $request->merge([
                            "type" => $action->type,
                            "loan_id" => $loan->id,
                            "mileage_beginning" => 0,
                        ]);
                        $this->controller->complete(
                            $request,
                            $loan->id,
                            $action->id
                        );

                        Log::info(
                            "Completed $action->type on loan ID $loan->id."
                        );
                    } else {
                        Log::info(
                            "Autocancelling loan ID $loan->id on $action->type action..."
                        );
                        $loan->cancel()->save();
                        Log::info("Canceled loan ID $loan->id.");
                    }
                }
            }

            /*
              Handovers:

              Complete handover if takeover is not contested
            */
            foreach ($loan->actions as $action) {
                if (
                    "handover" == $action->type &&
                    "in_process" == $action->status
                ) {
                    Log::info(
                        "Autocompleting $action->type on loan ID $loan->id..."
                    );

                    $takeover = $loan->takeover()->first();

                    // Don't complete handover if takeover is contested
                    if ($takeover->isContested()) {
                        continue;
                    }

                    $request = new ActionRequest();
                    $request->setUserResolver(function () use ($loan) {
                        return $loan->borrower->user;
                    });
                    $request->merge([
                        "type" => $action->type,
                        "loan_id" => $loan->id,
                        "purchases_amount" => 0,
                        "mileage_end" =>
                            $takeover->mileage_beginning +
                            $loan->estimated_distance,
                    ]);
                    $this->controller->complete(
                        $request,
                        $loan->id,
                        $action->id
                    );

                    Log::info("Completed $action->type on loan ID $loan->id.");
                }
            }

            // Completed handovers will not change loan status. Not necessary to refresh loan.

            /*
              Payments:

              Complete if balance is sufficient.
            */
            foreach ($loan->actions as $action) {
                if (
                    "payment" == $action->type &&
                    "in_process" == $action->status
                ) {
                    $takeover = $loan->takeover()->first();
                    $handover = $loan->handover()->first();

                    // Don't complete payment if takeover or handover is contested
                    if ($takeover->isContested() || $handover->isContested()) {
                        continue;
                    }

                    $totalActualCost = $loan->total_actual_cost;

                    if (
                        floatval($loan->borrower->user->balance) >=
                        $totalActualCost
                    ) {
                        Log::info(
                            "Autocompleting $action->type on loan ID $loan->id..."
                        );

                        $request = new ActionRequest();
                        $request->setUserResolver(function () use ($loan) {
                            return $loan->borrower->user;
                        });
                        $request->merge([
                            "type" => $action->type,
                            "loan_id" => $loan->id,
                            "platform_tip" => $loan->platform_tip,
                            "automated" => true,
                        ]);

                        $this->controller->complete(
                            $request,
                            $loan->id,
                            $action->id
                        );

                        Log::info(
                            "Completed $action->type on loan ID $loan->id."
                        );
                    } else {
                        Log::info(
                            "Not autocompleting $action->type on loan ID $loan->id " .
                                "because the user balance is less than the total actual cost " .
                                "({$loan->borrower->user->balance} < $totalActualCost)..."
                        );
                    }
                }
            }
        }

        Log::info("Completed actions autocompletion command.");
    }

    /*
       This function quickly fetches a supreset of the candidate loans to be
       completed or canceled. They will then be checked individually.

       The key word here is "superset".
    */
    public static function getActiveLoansScheduledToReturnBefore($datetime)
    {
        $loans = Loan::whereRaw("status = 'in_process'")

            // Duration, not accounting for extensions. It is not expected that
            // there will be many extensions and we can afford to load them and
            // check them individually.
            ->whereRaw(
                "departure_at + duration_in_minutes * interval '1 minute' <= ?",
                [$datetime]
            )
            // Load along with all actions. They'll be needed.
            ->with("actions")
            ->get();

        return $loans;
    }
}
