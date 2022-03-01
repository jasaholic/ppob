<?php

Route::group(['prefix' => 'v1', 'namespace' => 'V1', 'middleware' => ['auth:api', 'api-v1']], function() {
    
    Route::get('/balance', 'UserController@balance');
    
    Route::group(['prefix' => 'transaction'], function() {
        
        Route::post('/prabayar/create', 'TransactionController@createPrabayar');
        Route::get('/prabayar/history', 'TransactionController@historyPrabayar');
        Route::get('/prabayar/detail/{trx_id}', 'TransactionController@detailPrabayar');
        
        Route::post('/pascabayar/check', 'TransactionController@checkPascabayar');
        Route::post('/pascabayar/pay', 'TransactionController@payPascabayar');
        Route::get('/pascabayar/history', 'TransactionController@historyPascabayar');
        Route::get('/pascabayar/detail/{trx_id}', 'TransactionController@detailPascabayar');
        
    });
    
    Route::group(['prefix' => 'product'], function() {
        
        Route::get('/prabayar/category', 'ProductController@categoryPrabayar');
        Route::get('/prabayar/operator', 'ProductController@operatorPrabayar');
        Route::get('/prabayar', 'ProductController@prabayar');
        
        Route::get('/pascabayar/category', 'ProductController@categoryPascabayar');
        Route::get('/pascabayar/operator', 'ProductController@operatorPascabayar');
        Route::get('/pascabayar', 'ProductController@pascabayar'); 
    });
});