<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;

abstract class BaseMailable extends Mailable
{
    public function send($mailer) {
                             // Add some extra parameters to the message in
                             // order to write them in the logs.
        $this->withSwiftMessage(function ($message) {
            $message->mailable_class = get_class($this);
            $message->sent_at = date('Y-m-d H:i:s');
        });

        parent::send($mailer);
    }
}
