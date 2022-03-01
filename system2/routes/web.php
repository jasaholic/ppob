<?php
Route::post('/captcha', function() {
    $captcha = Captcha::chars('0123456789')->length(4)->size(130, 50)->generate();
    return response()->json([
        'success'   => true,
        'id'        => $captcha->id(),
        'image'     => $captcha->image()
        ]);
});



Route::group(['prefix' => 'webhook'], function() {
    Route::post('/digiflazz', 'DigiflazzWebhookController@listen'); 
 });

Route::get('',function(){
   phpinfo();
});

Route::get('/print/{id}.pdf', 'PrintTransaksiController@printShow');
//========================================= Route Callback PaymentTripay ===================================
Route::post('callback', 'Member\PaymentTripayController@callbackPaymentTripay');
//====================================== End Route Callback PaymentTripay ==================================

//============================================ Route Landing Page ==========================================
Route::get('/',function(){
	return redirect('/member');
});
//Route::get('/', 'HomeController@index');
Route::get('/about','HomeController@about');
Route::get('/tos','HomeController@tos');
Route::get('privacy-policy','HomeController@privacy_policy');
Route::get('/cara-transaksi', 'HomeController@caraTransaksi');
Route::get('/price/pembelian/{slug}', 'HomeController@pricePembelian');
Route::get('/price/pembayaran/{slug}', 'HomeController@pricePembayaran');
Route::get('/deposit', 'HomeController@deposit');
Route::get('/testimonial', 'HomeController@testimonial');
Route::get('/faq', 'HomeController@faq');
Route::post('/messages', 'HomeController@sendMessage');
Route::get('/api-docs', 'HomeController@apiDocs');
//========================================= End Route Landing Page =========================================

//========================================= Route Ajax Landing Page ========================================
Route::group(['prefix' => 'process'], function() {
    Route::get('/findproduct', 'HomeController@findproduct');
    Route::get('/findproduct/pembayaran', 'HomeController@findproductPembayaran');
    Route::get('/prefixproduct', 'HomeController@prefixproduct');
    Route::get('/getoperator', 'HomeController@getoperator');
});
//======================================= End Route Ajax Landing Page ======================================

//===================================== Route Transaction Landing Page =====================================
Route::post('voucher/generate-code', 'Admin\VoucherController@generateCode')->name('voucher.generateCode');
Route::get('/transaksi-pembayaran/process', 'Member\PembayaranController@transaksiProcess');
Route::get('/transaksi/process', 'Member\PembelianController@transaksiProcess');
//================================= End Route Transaction Landing Page =====================================


//=========================================== Route Auth ===================================================

Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::get('logout', 'Auth\LoginController@logout')->name('logout');

Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');


Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\RegisterController@register');
//======================================= End Route Auth ===================================================