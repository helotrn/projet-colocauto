<?php

use Illuminate\Http\Request;

Route::prefix('v1')->group(function () {
    Route::get('/', 'StaticController@blank');
    Route::post('auth/login', 'AuthController@login');

    Route::middleware('auth:api')->group(function () {
        Route::prefix('communities')->group(function () {
            Route::get('/', 'CommunityController@index');
            Route::post('/', 'CommunityController@create');
            Route::get('/{id}', 'CommunityController@retrieve');
            Route::put('/{id}', 'CommunityController@update');
            Route::delete('/{id}', 'CommunityController@delete');
        });

        Route::prefix('auth')->group(function () {
            Route::get('user', 'UserController@getUser');
            Route::put('logout', 'AuthController@logout');
        });
    });
});
