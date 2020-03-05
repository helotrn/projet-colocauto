<?php

Route::get('/{any?}', 'StaticController@app')->where('any', '.*')->name('app');
