<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;

abstract class BaseMailable extends Mailable
{
    public function send($mailer)
    {
        $mailableClass = get_class($this);
        // Prepare a semicolon-separated string of email
        // addresses.
        $emailAddresses = implode(
            "; ",
            array_map(function ($recipient) {
                return $recipient["address"];
            }, $this->to)
        );

        // Add some extra parameters to the message in
        // order to write them in the logs.
        $this->withSwiftMessage(function ($message) use ($mailableClass) {
            $message->mailable_class = $mailableClass;
            $message->sent_at = date("Y-m-d H:i:s");
        });

        Log::channel("mail")->info(
            "OK Sending $mailableClass to {$emailAddresses}"
        );

        parent::send($mailer);
    }
}
