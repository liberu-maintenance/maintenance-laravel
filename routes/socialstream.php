<?php

use Illuminate\Support\Facades\Route;

if (class_exists(\JoelButcher\Socialstream\Http\Controllers\OAuthController::class)) {
    Route::group(['middleware' => config('socialstream.middleware', ['web'])], function () {
        Route::get('/oauth/{provider}', [\JoelButcher\Socialstream\Http\Controllers\OAuthController::class, 'redirect'])->name('oauth.redirect');
        Route::match(['get', 'post'], '/oauth/{provider}/callback', [\JoelButcher\Socialstream\Http\Controllers\OAuthController::class, 'callback'])->name('oauth.callback');
    });
}
