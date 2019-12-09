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
        
        Route::prefix('bikes')->group(function () {
            Route::get('/', 'BikeController@index');
            Route::post('/', 'BikeController@create');
            Route::get('/{id}', 'BikeController@retrieve');
            Route::put('/{id}', 'BikeController@update');
            Route::delete('/{id}', 'BikeController@delete');
        });

        Route::prefix('billable-items')->group(function () {
            Route::get('/', 'BillableItemController@index');
            Route::post('/', 'BillableItemController@create');
            Route::get('/{id}', 'BillableItemController@retrieve');
            Route::put('/{id}', 'BillableItemController@update');
            Route::delete('/{id}', 'BillableItemController@delete');
        });

        Route::prefix('borrowers')->group(function () {
            Route::get('/', 'BorrowerController@index');
            Route::post('/', 'BorrowerController@create');
            Route::get('/{id}', 'BorrowerController@retrieve');
            Route::put('/{id}', 'BorrowerController@update');
            Route::delete('/{id}', 'BorrowerController@delete');
        });

        Route::prefix('cars')->group(function () {
            Route::get('/', 'CarController@index');
            Route::post('/', 'CarController@create');
            Route::get('/{id}', 'CarController@retrieve');
            Route::put('/{id}', 'CarController@update');
            Route::delete('/{id}', 'CarController@delete');
        });

        Route::prefix('communities')->group(function () {
            Route::get('/', 'CommunityController@index');
            Route::post('/', 'CommunityController@create');
            Route::get('/{id}', 'CommunityController@retrieve');
            Route::put('/{id}', 'CommunityController@update');
            Route::delete('/{id}', 'CommunityController@delete');
        });

        Route::prefix('extensions')->group(function () {
            Route::get('/', 'ExtensionController@index');
            Route::post('/', 'ExtensionController@create');
            Route::get('/{id}', 'ExtensionController@retrieve');
            Route::put('/{id}', 'ExtensionController@update');
            Route::delete('/{id}', 'ExtensionController@delete');
        });

        Route::prefix('files')->group(function () {
            Route::get('/', 'FileController@index');
            Route::post('/', 'FileController@create');
            Route::get('/{id}', 'FileController@retrieve');
            Route::put('/{id}', 'FileController@update');
            Route::delete('/{id}', 'FileController@delete');
        });

        Route::prefix('handovers')->group(function () {
            Route::get('/', 'HandoverController@index');
            Route::post('/', 'HandoverController@create');
            Route::get('/{id}', 'HandoverController@retrieve');
            Route::put('/{id}', 'HandoverController@update');
            Route::delete('/{id}', 'HandoverController@delete');
        });

        Route::prefix('images')->group(function () {
            Route::get('/', 'ImageController@index');
            Route::post('/', 'ImageController@create');
            Route::get('/{id}', 'ImageController@retrieve');
            Route::put('/{id}', 'ImageController@update');
            Route::delete('/{id}', 'ImageController@delete');
        });

        Route::prefix('incidents')->group(function () {
            Route::get('/', 'IncidentController@index');
            Route::post('/', 'IncidentController@create');
            Route::get('/{id}', 'IncidentController@retrieve');
            Route::put('/{id}', 'IncidentController@update');
            Route::delete('/{id}', 'IncidentController@delete');
        });

        Route::prefix('intentions')->group(function () {
            Route::get('/', 'IntentionController@index');
            Route::post('/', 'IntentionController@create');
            Route::get('/{id}', 'IntentionController@retrieve');
            Route::put('/{id}', 'IntentionController@update');
            Route::delete('/{id}', 'IntentionController@delete');
        });

        Route::prefix('loan')->group(function () {
            Route::get('/', 'LoanController@index');
            Route::post('/', 'LoanController@create');
            Route::get('/{id}', 'LoanController@retrieve');
            Route::put('/{id}', 'LoanController@update');
            Route::delete('/{id}', 'LoanController@delete');
        });

        Route::prefix('owners')->group(function () {
            Route::get('/', 'OwnerController@index');
            Route::post('/', 'OwnerController@create');
            Route::get('/{id}', 'OwnerController@retrieve');
            Route::put('/{id}', 'OwnerController@update');
            Route::delete('/{id}', 'OwnerController@delete');
        });

        Route::prefix('padlocks')->group(function () {
            Route::get('/', 'PadlockController@index');
            Route::post('/', 'PadlockController@create');
            Route::get('/{id}', 'PadlockController@retrieve');
            Route::put('/{id}', 'PadlockController@update');
            Route::delete('/{id}', 'PadlockController@delete');
        });

        Route::prefix('payments')->group(function () {
            Route::get('/', 'PaymentController@index');
            Route::post('/', 'PaymentController@create');
            Route::get('/{id}', 'PaymentController@retrieve');
            Route::put('/{id}', 'PaymentController@update');
            Route::delete('/{id}', 'PaymentController@delete');
        });

        Route::prefix('payment-methods')->group(function () {
            Route::get('/', 'PaymentMethodController@index');
            Route::post('/', 'PaymentMethodController@create');
            Route::get('/{id}', 'PaymentMethodController@retrieve');
            Route::put('/{id}', 'PaymentMethodController@update');
            Route::delete('/{id}', 'PaymentMethodController@delete');
        });

        Route::prefix('pricings')->group(function () {
            Route::get('/', 'PricingController@index');
            Route::post('/', 'PricingController@create');
            Route::get('/{id}', 'PricingController@retrieve');
            Route::put('/{id}', 'PricingController@update');
            Route::delete('/{id}', 'PricingController@delete');
        });

        Route::prefix('tags')->group(function () {
            Route::get('/', 'TagController@index');
            Route::post('/', 'TagController@create');
            Route::get('/{id}', 'TagController@retrieve');
            Route::put('/{id}', 'TagController@update');
            Route::delete('/{id}', 'TagController@delete');
        });

        Route::prefix('takeovers')->group(function () {
            Route::get('/', 'TakeoverController@index');
            Route::post('/', 'TakeoverController@create');
            Route::get('/{id}', 'TakeoverController@retrieve');
            Route::put('/{id}', 'TakeoverController@update');
            Route::delete('/{id}', 'TakeoverController@delete');
        });

        Route::prefix('trailers')->group(function () {
            Route::get('/', 'TrailerController@index');
            Route::post('/', 'TrailerController@create');
            Route::get('/{id}', 'TrailerController@retrieve');
            Route::put('/{id}', 'TrailerController@update');
            Route::delete('/{id}', 'TrailerController@delete');
        });

        Route::prefix('users')->group(function () {
            Route::get('/', 'UserController@index');
            Route::post('/', 'UserController@create');
            Route::get('/{id}', 'UserController@retrieve');
            Route::put('/{id}', 'UserController@update');
            Route::delete('/{id}', 'UserController@delete');
        });
    });
});
