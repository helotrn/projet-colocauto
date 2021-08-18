<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

class SchedulerController extends Controller
{
    public static function nokeLocks(Request $request)
    {
      return $request;
        Artisan::call("noke:sync:locks");
    }

    public static function nokeUsers()
    {
        Artisan::call("noke:sync:users");
    }

    public static function nokeLoans()
    {
        Artisan::call("noke:sync:loans");
    }
    public static function actionsComplete()
    {
        Artisan::call("actions:complete");
    }
    public static function emailLoanUpcoming()
    {
        Artisan::call("email:loan:upcoming");
    }
    public static function emailPrePayement()
    {
        Artisan::call("email:loan:pre_payment_missing");
    }
}
