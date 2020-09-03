<?php

namespace App\Listeners;

use App\Mail\InvoicePaid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendInvoiceEmail
{
    public function handle($event) {
        Mail::to($event->user->email, $event->user->name . ' ' . $event->user->last_name)
            ->queue(new InvoicePaid(
                $event->user,
                $event->invoice,
                $event->title,
                $event->text,
                $event->subject
            ));
    }
}
