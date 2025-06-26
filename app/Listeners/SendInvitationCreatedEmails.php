<?php

namespace App\Listeners;

use App\Events\InvitationCreatedEvent;
use App\Mail\Invitation\Created as InvitationCreated;
use App\Mail\Invitation\CreatedForAdmin as InvitationCreatedForAdmin;
use App\Mail\Invitation\CreatedForAutonomy as InvitationCreatedForAutonomy;
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
        if( $event->invitation->for_community_admin ) {
            $mailToSend = new InvitationCreatedForAdmin($event->invitation->email, $event->invitation->community, $event->invitation->token);
        } else if( $event->invitation->community ){
            $mailToSend = new InvitationCreated($event->invitation->email, $event->invitation->community, $event->invitation->token);
        } else {
            $mailToSend = new InvitationCreatedForAutonomy($event->invitation->email, $event->invitation->token);
        }
        Mail::to($event->invitation->email)->queue($mailToSend);
    }
}
