<?php

namespace App\Console\Commands;

use App\Http\Controllers\ActionController;
use App\Http\Requests\Action\ActionRequest;
use App\Models\Action;
use Illuminate\Console\Command;

class ActionsComplete extends Command
{
    protected $signature = 'actions:complete';

    protected $description = 'Complete actions after 48 hours of inactivity';

    private $controller;

    public function __construct(ActionController $controller) {
        parent::__construct();

        $this->controller = $controller;
    }

    public function handle() {
        // Intentions have to be confirmed by owner
        // Pre-payments have to be made by borrower
        // Incidents have to be managed by an admin
        $finishableActions = Action::whereIn(
            'type',
            ['takeover', 'handover', 'extension', 'payment']
        )
            ->where('status', 'in_process')
            ->where('created_at', '<=', (new \DateTime('-48hours'))->format('Y-m-d H:i:s'))
            ->with('loan', 'loan.loanable', 'loan.borrower', 'loan.borrower.user')
            ->get();

        foreach ($finishableActions as $action) {
            $loan = $action->loan;

            switch ($action->type) {
                case 'handover':
                    $takeover = $loan->takeover()->first();

                    $request = new ActionRequest;
                    $request->setUserResolver(function () use ($loan) {
                        return $loan->borrower->user;
                    });
                    $request->merge([
                        'type' => $action->type,
                        'loan_id' => $loan->id,
                        'mileage_end' => $takeover->mileage_beginning
                            + $loan->estimated_distance,
                    ]);
                    $this->controller->complete($request, $loan->id, $action->id);
                    break;
                case 'takeover':
                    $request = new ActionRequest;
                    $request->setUserResolver(function () use ($loan) {
                        return $loan->borrower->user;
                    });
                    $request->merge([
                        'type' => $action->type,
                        'loan_id' => $loan->id,
                        'mileage_beginning' => 0,
                    ]);
                    $this->controller->complete($request, $loan->id, $action->id);
                    break;
                case 'extension':
                    $request = new ActionRequest;
                    $request->setUserResolver(function () use ($loan) {
                        // FIXME is this right? shouldn't it be the owner?
                        return $loan->borrower->user;
                    });
                    $request->merge([
                        'type' => $action->type,
                        'loan_id' => $loan->id,
                        'new_duration' => $action->new_duration,
                    ]);
                    $this->controller->complete($request, $loan->id, $action->id);
                    break;
                case 'payment':
                    $totalActualCost = $loan->total_actual_cost;

                    if (floatval($loan->borrower->user->balance) >= $totalActualCost) {
                        $request = new ActionRequest;
                        $request->setUserResolver(function () use ($loan) {
                            return $loan->borrower->user;
                        });
                        $request->merge([
                            'type' => $action->type,
                            'loan_id' => $loan->id,
                            'platform_tip' => $loan->platform_tip,
                            'automated' => true,
                        ]);
                        $this->controller->complete($request, $loan->id, $action->id);
                    }
                    break;
                default:
                    break;
            }
        }
    }
}
