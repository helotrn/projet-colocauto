<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class StripeServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton("stripe", function ($app) {
            return new \App\Services\StripeService(
                config("services.stripe.secret")
            );
        });
    }
}
