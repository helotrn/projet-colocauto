<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MattermostServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton("mattermosthelper", function ($app) {
            return new \App\Services\MattermostService(
                config("services.mattermost")
            );
        });
    }
}
