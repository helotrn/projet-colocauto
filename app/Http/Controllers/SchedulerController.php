<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Response;
use App\Services\NokeService;
use GuzzleHttp\Client;
use App\Console\Commands\NokeSyncLoans;
use App\Console\Commands\NokeSyncLocks;
use App\Console\Commands\NokeSyncUsers;

class SchedulerController extends Controller
{
    public static function nokeLocks($appKey, Client $client, NokeService $service)
    {
        if ($appKey != env("APP_KEY")) {
            return new Response("Wrong app key", 403);
        }
        $command = new NokeSyncLocks($client, $service);
        $command->handle();
    }

    public static function nokeUsers($appKey, Client $client, NokeService $service)
    {
        if ($appKey != env("APP_KEY")) {
            return new Response("Wrong app key", 403);
        }
        $command = new NokeSyncUsers($client, $service);
        $command->handle();
    }

    public static function nokeLoans($appKey, Client $client, NokeService $service)
    {
        if ($appKey != env("APP_KEY")) {
            return new Response("Wrong app key", 403);
        }
        $command = new NokeSyncLoans($client, $service);
        $command->handle();
    }
    public static function actionsComplete($appKey)
    {
        if ($appKey != env("APP_KEY")) {
            return new Response("Wrong app key", 403);
        }
        Artisan::call("actions:complete");
    }
    public static function emailLoanUpcoming($appKey)
    {
        if ($appKey != env("APP_KEY")) {
            return new Response("Wrong app key", 403);
        }
        Artisan::call("email:loan:upcoming");
    }
    public static function emailPrePayement($appKey)
    {
        if ($appKey != env("APP_KEY")) {
            return new Response("Wrong app key", 403);
        }
        Artisan::call("email:loan:pre_payment_missing");
    }
}
