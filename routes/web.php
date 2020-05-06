<?php

Route::get('/borrower/{any?}', 'AssetController@borrowerFile')
    ->where('any', '.*')
    ->name('assets.borrower');

Route::get('/communityuser/{any?}', 'AssetController@communityUserFile')
    ->where('any', '.*')
    ->name('assets.communityUser');

Route::get('/user/{any?}', 'AssetController@userFile')
    ->where('any', '.*')
    ->name('assets.user');

Route::get('/{any?}', 'StaticController@app')->where('any', '.*')->name('app');
