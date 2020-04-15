<?php

Route::get('/', 'StaticController@app')->where('any', '.*')->name('app');
