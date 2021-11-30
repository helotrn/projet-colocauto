<?php

namespace App\Mail;

abstract class MandrillMailable extends BaseMailable
{
    public $template = "base";
    public $trackable = false;
    public $templateVars = [];
    public $subject;
    public $body = "";
    public $text = "";

    public $raw = false;

    public function send($mailer)
    {
        $this->withSwiftMessage(function ($message) {
            $message->template = $this->template;
            $message->trackable = $this->trackable;
            $message->templateVars = $this->templateVars;
        });

        parent::send($mailer);
    }

    public function build()
    {
        return $this->subject($this->subject)
            ->view("emails.mandrill")
            ->text("emails.mandrill_text")
            ->with($this->templateVars);
    }
}
