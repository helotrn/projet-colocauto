<?php

namespace App\Console\Commands;

use App\Http\Controllers\ActionController;
use App\Http\Controllers\PaymentController;
use App\Http\Requests\Action\ActionRequest;
use App\Models\Loan;
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

        // TODO: Usages of HTTP Controller here should be migirated to calling the controller directly.

        $loanExpirationTime = CarbonImmutable::now()->subHours(48);
        $loans = self::getActiveLoansScheduledToReturnBefore(
            $loanExpirationTime
        );

        foreach ($loans as $loan) {
            // Autocomplete nothing if loan is contested
            if (
                ($loan->handover && $loan->handover->isContested()) ||
                ($loan->takeover && $loan->takeover->isContested())
            ) {
                continue;
            }

            // Cancel all ongoing extensions
            foreach ($loan->extensions as $extension) {
                if ($extension->status === "in_process") {
                    Log::info("Canceling extension on loan ID $loan->id...");

                    $request = new ActionRequest();
                    $request->merge([
                        "type" => "extension",
                        "loan_id" => $loan->id,
                        "new_duration" => $extension->new_duration,
                    ]);

                    $this->controller->cancel(
                        $request,
                        $loan->id,
                        $extension->id
                    );

                    Log::info("Canceled extension on loan ID $loan->id.");
                }
            }

            if ($loan->intention && $loan->intention->status === "in_process") {
                self::cancelLoan($loan, "intention");
                continue;
            }

            if (
                $loan->prePayment &&
                $loan->prePayment->status === "in_process"
            ) {
                self::cancelLoan($loan, "pre_payment");
                continue;
            }

            if ($loan->takeover && $loan->takeover->status === "in_process") {
                self::cancelLoan($loan, "takeover");
                continue;
            }

            /*
              Handovers:

              Complete handover if takeover is not contested
            */
            if ($loan->handover && $loan->handover->status === "in_process") {
                Log::info("Autocompleting handover on loan ID $loan->id...");

                $request = new ActionRequest();
                $request->setUserResolver(function () use ($loan) {
                    return $loan->borrower->user;
                });
                $request->merge([
                    "type" => "handover",
                    "loan_id" => $loan->id,
                    "purchases_amount" => 0,
                    "mileage_end" =>
                        $loan->takeover->mileage_beginning +
                        $loan->estimated_distance,
                ]);
                $this->controller->complete(
                    $request,
                    $loan->id,
                    $loan->handover->id
                );

                Log::info("Completed handover on loan ID $loan->id.");
            }

            // Need to refresh since we check the state of the loan before paying.
            $loan->refresh();
            // Completed handovers will not change loan status. Not necessary to refresh loan.
            if ($loan->canBePaid()) {
                Log::info("Autocompleting payment on loan ID $loan->id...");
                PaymentController::pay($loan, true);
                Log::info("Completed payment on loan ID $loan->id.");
            } else {
                Log::info("Not autocompleting payment on loan ID $loan->id.");
            }
        }

        Log::info("Completed actions autocompletion command.");
    }

    public static function getActiveLoansScheduledToReturnBefore($datetime)
    {
        return Loan::where("status", "=", "in_process")
            ->where("actual_return_at", "<=", $datetime)
            ->get();
    }

    /**
     * Cancels the loan and logs which action was ongoing.
     * @param Loan $loan
     * @param string $action Ongoing action
     * @return void
     */
    public static function cancelLoan(Loan $loan, string $action): void
    {
        Log::info("Autocancelling loan ID $loan->id on $action action...");
        $loan->cancel()->save();
        Log::info("Canceled loan ID $loan->id.");
    }
}
