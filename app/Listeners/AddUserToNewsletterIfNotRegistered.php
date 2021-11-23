<?php

namespace App\Listeners;

use App\Events\RegistrationSubmittedEvent;
use Mailchimp;

class AddUserToNewsletterIfNotRegistered
{
    public function handle($event)
    {
        // Any event with user would be adequate.
        if ($event instanceof RegistrationSubmittedEvent) {
            $mailchimpService = Mailchimp::addToListOrUpdate($event->user);
        }
    }
}
