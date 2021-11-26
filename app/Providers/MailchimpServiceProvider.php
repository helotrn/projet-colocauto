<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MailchimpServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton("mailchimp", function ($app) {
            return new \App\Services\MailchimpService(
                config("services.mailchimp")
            );
        });
    }
}
