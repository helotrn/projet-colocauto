<?php

use Illuminate\Http\Request;
use Molotov\Utils\RouteHelper;

Route::prefix('v1')->group(function () {
    Route::get('/', 'StaticController@blank');
    Route::get('/status', 'StaticController@status');
    Route::get('/stats', 'StaticController@stats');

    Route::post('auth/login', 'AuthController@login');
    Route::post('auth/register', 'AuthController@register');
    Route::post('auth/password/request', 'AuthController@passwordRequest');
    Route::post('auth/password/reset', 'AuthController@passwordReset');

    Route::middleware(['auth:api', 'transaction'])->group(function () {
        Route::prefix('auth')->group(function () {
            Route::get('user', 'AuthController@retrieveUser');
            Route::put('user', 'AuthController@updateUser');
            Route::put('user/submit', 'AuthController@submitUser');
            Route::get('user/balance', 'AuthController@getUserBalance');
            Route::put('user/balance', 'AuthController@addToUserBalance');
            Route::put('user/claim', 'AuthController@claimUserBalance');
            Route::put('logout', 'AuthController@logout');
        });

        foreach ([
            'action',
            'bike',
            'car',
            'community',
            'file',
            'image',
            'invoice',
            'loan',
            'loanable',
            'padlock',
            'payment_method',
            'tag',
            'trailer',
            'user'
        ] as $entity) {
            RouteHelper::resource($entity);
        }

        RouteHelper::retrieve('borrower');
        RouteHelper::index('borrower');
        RouteHelper::retrieve('owner');
        RouteHelper::index('owner');

        Route::post('users/{user_id}/password', 'UserController@updatePassword');
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

        Route::get('/users/{user_id}/borrower', 'UserController@retrieveBorrower');
        Route::put('/users/{user_id}/borrower/approve', 'UserController@approveBorrower');
        Route::put('/users/{user_id}/borrower/suspend', 'UserController@suspendBorrower');
        Route::delete('/users/{user_id}/borrower/suspend', 'UserController@unsuspendBorrower');

        RouteHelper::subresource('user', 'tag');

        RouteHelper::subresource('community', 'user');

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

        Route::get('/loans/{loan_id}/borrower', 'LoanController@retrieveBorrower');
        Route::put('/loans/{loan_id}/cancel', 'LoanController@cancel');

        Route::post('/loans/{loan_id}/actions', 'LoanController@createAction');
        Route::get('/loans/{loan_id}/actions/{action_id}', 'LoanController@retrieveAction');
        Route::put('/loans/{loan_id}/actions/{action_id}/complete', 'ActionController@complete');
        Route::put('/loans/{loan_id}/actions/{action_id}/cancel', 'ActionController@cancel');

        Route::put('/pricings/{id}/evaluate', 'PricingController@evaluate')
          ->name('pricings.evaluate');

        Route::get('/loanables/{id}/test', 'LoanableController@test');
    });

    Route::get('/{any?}', 'StaticController@notFound')->where('any', '.*');
});
