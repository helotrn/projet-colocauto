<?php

namespace App\Providers;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class NokeServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton("noke", function ($app) {
            return new \App\Services\NokeService(new Client());
        });
    }
}
