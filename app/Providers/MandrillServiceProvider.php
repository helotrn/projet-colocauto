<?php

namespace App\Providers;

use App\Mail\Transport\MandrillTransport;
use GuzzleHttp\Client;
use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;

class MandrillServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->resolving(MailManager::class, function (
            MailManager $manager
        ) {
            // data_get($config, "secret") ?: "na" was on line 22 before for obscur reasons
            $manager->extend("mandrill", function () {
                $config = $this->app["config"]->get("services.mandrill", []);
                return new MandrillTransport(
                    new Client($config),
                    $config["secret"]
                );
            });
        });
    }
}
