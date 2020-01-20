<?php

namespace App\Utils;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;

class RouteHelper
{
    public static function resource($slug, $controller = null) {
        $camelSlug = Str::camel(str_replace('-', '_', $slug));
        $pluralSlug = Pluralizer::plural($slug);

        $controller = $controller ?: ucfirst("{$camelSlug}Controller");

        Route::prefix($pluralSlug)->group(function () use ($controller, $pluralSlug) {
            Route::get('/', "$controller@index")->name("$pluralSlug.index");
            Route::post('/', "$controller@create")->name("$pluralSlug.create");
            Route::get('/{id}', "$controller@retrieve")->name("$pluralSlug.retrieve");
            Route::put('/{id}', "$controller@update")->name("$pluralSlug.update");
            Route::delete('/{id}', "$controller@destroy")->name("$pluralSlug.destroy");
        });
    }
}
