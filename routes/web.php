<?php

Route::get('/', 'StaticController@redirectToSolon')->name('placeholder');
Route::get('/', 'StaticController@app')->name('app');
Route::get('/login', 'StaticController@app')->name('login');
