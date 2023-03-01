<?php

namespace App\Listeners;

use App\Events\BorrowerSuspendedEvent;

class CancelFutureLoans
{
    public function handle(BorrowerSuspendedEvent $event)
    {
        foreach($event->user->loans->where("status", "=", "in_process") as $loan) {
            $loan->cancel();
            $loan->save();
        }
    }
}
