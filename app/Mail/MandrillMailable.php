<?php

namespace App\Mail;

abstract class MandrillMailable extends BaseMailable
{
    public $template = "base";
    public $trackable = false;
    public $templateVars = [];

    public $raw = false;

    public function send($mailer)
    {
        $this->withSwiftMessage(function ($message) {
            $message->template = $this->template;
            $mesage->trackable = $this->trackable;
            $message->templateVars = $this->templateVars;
        });

        parent::send($mailer);
    }
}
