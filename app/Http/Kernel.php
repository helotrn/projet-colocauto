<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \App\Http\Middleware\Cors::class,
    ];

    protected $middlewareGroups = [
        "web" => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        "api" => ["throttle:120,1", "bindings"],
    ];

    protected $routeMiddleware = [
        "auth" => \App\Http\Middleware\Authenticate::class,
        "auth.basic" =>
            \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        "bindings" => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        "cache.headers" => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        "can" => \Illuminate\Auth\Middleware\Authorize::class,
        "guest" => \App\Http\Middleware\RedirectIfAuthenticated::class,
        "signed" => \Illuminate\Routing\Middleware\ValidateSignature::class,
        "throttle" => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        "transaction" => \Molotov\Middleware\WrapInTransaction::class,
        "verified" => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];

    protected $middlewarePriority = [
        \App\Http\Middleware\Authenticate::class,
        \Illuminate\Routing\Middleware\ThrottleRequests::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];
}
