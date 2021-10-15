<?php

namespace App\Console\Commands;

use App\Http\Controllers\ActionController;
use App\Http\Requests\Action\ActionRequest;
use App\Models\Action;
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

        // The following action cannot be completed...
        // Intentions: have to be confirmed by owner
        // Pre-payments: have to be made by borrower
        // Incidents: have to be managed by an admin

        $finishableActions = Action::whereIn("type", [
            /*'extension',*/
            /*'handover',*/
            "payment",
            "takeover",
        ])
            ->where("status", "in_process")
            ->where(
                "created_at",
                "<=",
                (new \DateTime("-48hours"))->format("Y-m-d H:i:s")
            )
            ->whereHas("loan")
            ->with(
                "loan",
                "loan.loanable",
                "loan.borrower",
                "loan.borrower.user"
            )
            ->get();

        foreach ($finishableActions as $action) {
            $loan = $action->loan;

            try {
                switch ($action->type) {
                    case "takeover":
                        Log::info(
                            "Autocancelling loan ID $loan->id because "
                                . "$action->type has not been completed..."
                        );

                        $loan->update([
                            'canceled_at' => new \DateTime(),
                        ]);

                        Log::info(
                            "Canceled loan ID $loan->id because "
                                . "$action->type has never been completed."
                        );
                        break;
                    case "handover":
                        Log::info(
                            "Autocompleting $action->type on loan ID $loan->id..."
                        );

                        $takeover = $loan->takeover()->first();

                        $request = new ActionRequest();
                        $request->setUserResolver(function () use ($loan) {
                            return $loan->borrower->user;
                        });
                        $request->merge([
                            "type" => $action->type,
                            "loan_id" => $loan->id,
                            "mileage_end" =>
                                $takeover->mileage_beginning +
                                $loan->estimated_distance,
                        ]);
                        $this->controller->complete(
                            $request,
                            $loan->id,
                            $action->id
                        );

                        Log::info(
                            "Autocompleted $action->type on loan ID $loan->id."
                        );
                        break;
                    case "payment":
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
                                "Autocompleted $action->type on loan ID $loan->id."
                            );
                        } else {
                            Log::warning(
                                "Not autocompleting $action->type on loan ID $loan->id " .
                                    "because the user balance is less than the total actual cost " .
                                    "({$loan->borrower->user->balance} < $totalActualCost)..."
                            );
                        }
                        break;
                    case "extension":
                        Log::info(
                            "Autocompleting $action->type on loan ID $loan->id..."
                        );

                        $request = new ActionRequest();
                        $request->setUserResolver(function () use ($loan) {
                            // FIXME is this right? shouldn't it be the owner?
                            return $loan->borrower->user;
                        });
                        $request->merge([
                            "type" => $action->type,
                            "loan_id" => $loan->id,
                            "new_duration" => $action->new_duration,
                        ]);
                        $this->controller->complete(
                            $request,
                            $loan->id,
                            $action->id
                        );

                        Log::info(
                            "Autocompleted $action->type on loan ID $loan->id."
                        );
                        break;
                    default:
                        break;
                }
            } catch (\Exception $e) {
                Log::error(
                    "Fatal error trying to autocomplete " .
                        "action ID $action->id: {$e->getMessage()}."
                );
            }
        }

        Log::info("Completed actions autocompletion command.");
    }
}
