<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->getMethod() == 'OPTIONS'){
            header('Access-Control-Allow-Origin: '.env('FRONTEND_URL', '*'));
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
            header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token, Origin, Authorization');
            header('Access-Control-Allow-Credentials: true');
            exit(0);
        }

        if (
            is_callable([$request, "header"])
        ) {
            $response = $next($request);

            return $response
                ->header("Access-Control-Allow-Origin", env('FRONTEND_URL', '*') )
                ->header(
                    "Access-Control-Allow-Methods",
                    "POST, GET, OPTIONS, PUT, DELETE"
                )
                ->header(
                    "Access-Control-Allow-Headers",
                    "Content-Type, X-Auth-Token, Origin, Authorization"
                );
        }

        return $next($request);
    }
}
