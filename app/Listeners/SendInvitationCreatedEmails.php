<?php

namespace App\Listeners;

use App\Events\InvitationCreatedEvent;
use App\Mail\Invitation\Created as InvitationCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendInvitationCreatedEmails
{
    /*
       Send invitation link so that the new user car create its account
    */
    public function handle(InvitationCreatedEvent $event)
    {
        Mail::to($event->invitation->email)->queue(
            new InvitationCreated($event->invitation->email, $event->invitation->community, $event->invitation->token)
        );
    }
}
