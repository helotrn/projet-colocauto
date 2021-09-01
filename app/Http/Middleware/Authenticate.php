<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as ParentAuthenticate;
use App\Http\Middleware\AuthFactory;
use Log;
use Cache;
use Auth;

class Authenticate extends ParentAuthenticate
{
    protected function authenticate($request, array $guards)
    {
        $user = Cache::remember(
            $request->headers->get("authorization"),
            600,
            function () use ($request, $guards) {
                parent::authenticate($request, $guards);
                return $request->user();
            }
        );
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        Auth::setUser($user);
    }

    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return route("login");
        }
    }
}
