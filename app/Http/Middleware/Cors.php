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
        /**
         * Cross-Origin Resource Sharing (CORS)
         * is only allowed for local
         * environment. */
        if (
            app()->environment() === "local" &&
            is_callable($request, "header")
        ) {
            $response = $next($request);

            return $response
                ->header("Access-Control-Allow-Origin", "*")
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
