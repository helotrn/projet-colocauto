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
            Route::get('user', 'AuthController@retrieveUser');
            Route::put('user', 'AuthController@updateUser');
            Route::put('user/submit', 'AuthController@submitUser');
            Route::get('user/balance', 'AuthController@getUserBalance');
            Route::put('user/balance', 'AuthController@addToUserBalance');
            Route::put('logout', 'AuthController@logout');
        });

        foreach ([
            'action',
            'bike',
            'bill',
            'borrower',
            'car',
            'community',
            'file',
            'image',
            'invoice',
            'loan',
            'loanable',
            'owner',
            'padlock',
            'payment_method',
            'pricing',
            'tag',
            'trailer',
            'user'
        ] as $entity) {
            RouteHelper::resource($entity);
        }

        Route::get('users/{user_id}/communities', 'UserController@indexCommunities');
        Route::get('users/{user_id}/communities/{community_id}', 'UserController@retrieveCommunity');
        Route::put(
            'users/{user_id}/communities/{community_id}',
            'UserController@createCommunityUser'
        );
        Route::delete(
            'users/{user_id}/communities/{community_id}',
            'UserController@deleteCommunityUser'
        );

        RouteHelper::subresource('user', 'tag');

        Route::get(
            'communities/{community_id}/users/{user_id}/tags',
            'CommunityController@indexCommunityUserTags'
        );
        Route::put(
            'communities/{community_id}/users/{user_id}/tags/{tag_id}',
            'CommunityController@updateCommunityUserTags'
        );
        Route::delete(
            'communities/{community_id}/users/{user_id}/tags/{tag_id}',
            'CommunityController@destroyCommunityUserTags'
        );

        Route::get('/loans/{loan_id}/actions/{action_id}', 'LoanController@retrieveAction');
        Route::put('/loans/{loan_id}/actions/{action_id}/complete', 'ActionController@complete');
        Route::put('/loans/{loan_id}/actions/{action_id}/cancel', 'ActionController@cancel');

        Route::put('/borrowers/{id}/approve', 'BorrowerController@approve');

        Route::put('/pricings/{id}/evaluate', 'PricingController@evaluate');

        Route::get('/loanables/{id}/test', 'LoanableController@test');
    });

    Route::get('/{any?}', 'StaticController@notFound')->where('any', '.*');
});
