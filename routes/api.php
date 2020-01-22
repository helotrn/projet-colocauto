<?php

use Illuminate\Http\Request;
use App\Utils\RouteHelper;

Route::prefix('v1')->group(function () {
    Route::get('/', 'StaticController@blank');
    Route::get('/status', 'StaticController@status');

    Route::post('auth/login', 'AuthController@login');
    Route::post('auth/register', 'AuthController@register');

    Route::middleware('auth:api')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::get('user', 'AuthController@getUser');
            Route::put('user', 'AuthController@updateUser');
            Route::put('logout', 'AuthController@logout');
        });

        foreach ([
            'bike',
            'bill',
            'billable-item',
            'borrower',
            'car',
            'community',
            'extension',
            'file',
            'handover',
            'image',
            'incident',
            'intention',
            'loan',
            'owner',
            'padlock',
            'payment',
            'payment-method',
            'pricing',
            'tag',
            'takeover',
            'trailer',
            'user'
        ] as $entity) {
            RouteHelper::resource($entity);
        }

        Route::get('users/{id}/communities', "UserController@getCommunities")
            ->name("users.getCommunities");

        Route::post('users/{id}/communities/{sub_id}', "UserController@associateToCommunity")
            ->name("users.associateToCommunity");

        Route::post('users/{id}/communities/{sub_id}', "UserController@dissociateFromCommunity")
            ->name("users.dissociateFromCommunity");
    });
});
