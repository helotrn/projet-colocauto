<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Response;

class SchedulerController extends Controller
{
    public static function nokeLocks($appKey)
    {
        if ($appKey != env("APP_KEY")) {
            return new Response("Wrong app key", 403);
        }
        Artisan::call("noke:sync:locks");
    }

    public static function nokeUsers($appKey)
    {
        if ($appKey != env("APP_KEY")) {
            return new Response("Wrong app key", 403);
        }
        Artisan::call("noke:sync:users");
    }

    public static function nokeLoans($appKey)
    {
        if ($appKey != env("APP_KEY")) {
            return new Response("Wrong app key", 403);
        }
        Artisan::call("noke:sync:loans");
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
