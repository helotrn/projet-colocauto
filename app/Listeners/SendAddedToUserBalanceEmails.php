<?php

namespace App\Listeners;

use App\Events\AddedToUserBalanceEvent;
use App\Mail\InvoicePaid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendAddedToUserBalanceEmails
{
    public function handle(AddedToUserBalanceEvent $event) {
        Mail::to($event->user->email, $event->user->full_name)
          ->queue(new InvoicePaid($event->user, $event->invoice));
    }
}
