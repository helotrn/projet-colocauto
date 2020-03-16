<?php

use Illuminate\Http\Request;
use Molotov\Utils\RouteHelper;

Route::prefix('v1')->group(function () {
    Route::get('/', 'StaticController@blank');
    Route::get('/status', 'StaticController@status');
    Route::get('/stats', 'StaticController@stats');

    Route::post('auth/login', 'AuthController@login');
    Route::post('auth/register', 'AuthController@register');

    Route::middleware('auth:api')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::get('user', 'AuthController@getUser');
            Route::put('user', 'AuthController@updateUser');
            Route::put('user/submit', 'AuthController@submitUser');
            Route::put('logout', 'AuthController@logout');
        });

        foreach ([
            'action',
            'bike',
            'bill',
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
            'loanable',
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

        Route::get('users/{user_id}/communities', 'UserController@getCommunities')
            ->name('users.getCommunities');

        Route::get('users/{user_id}/communities/{community_id}', 'UserController@retrieveCommunity')
            ->name('users.retrieveCommunity');

        Route::post(
            'users/{user_id}/communities/{community_id}',
            'UserController@createUserCommunity'
        )
            ->name('users.createUserCommunity');

        Route::delete('users/{user_id}/communities/{community_id}', 'UserController@deleteUserCommunity')
            ->name("users.deleteUserCommunity");

        Route::put('/loans/{loan_id}/actions/{action_id}/complete', 'ActionController@complete');
        Route::put('/loans/{loan_id}/actions/{action_id}/cancel', 'ActionController@cancel');

        Route::put('/borrowers/{id}/approve', 'BorrowerController@approve');

        Route::put('/pricings/{id}/evaluate', 'PricingController@evaluate');

        Route::get('/loanables/{id}/test', 'LoanableController@test');
    });
});
