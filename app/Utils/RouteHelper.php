<?php

namespace App\Utils;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Pluralizer;

class RouteHelper
{
    public static function resource($slug, $controller = null) {
        $controller = $controller ?: ucfirst("{$slug}Controller");
        Route::prefix(Pluralizer::plural($slug))->group(function () use ($controller) {
            Route::get('/', "$controller@index");
            Route::post('/', "$controller@create");
            Route::get('/{id}', "$controller@retrieve");
            Route::put('/{id}', "$controller@update");
            Route::delete('/{id}', "$controller@delete");
        });
    }
}
