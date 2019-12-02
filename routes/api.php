<?php

use Illuminate\Http\Request;

Route::prefix('v1')->group(function () {
    Route::get('/', 'StaticController@blank');
    Route::post('auth/login', 'AuthController@login');

    Route::middleware('auth:api')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::get('user', 'UserController@getUser');
            Route::put('logout', 'AuthController@logout');
        });
    });
});
