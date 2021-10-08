<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;

class MailController extends Controller
{
    public function subscribe($email)
    {
        $mailchimp = new \MailchimpMarketing\ApiClient();

        $mailchimp->setConfig([
            "apiKey" => env("MAILCHIMP_KEY"),
            "server" => env("MAILCHIMP_SERVER_PREFIX"),
        ]);

        $response = $mailchimp->ping->get();
        print_r($response);
    }
}
