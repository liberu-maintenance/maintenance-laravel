<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('home');
})->name('home');

// Named dashboard route — redirects authenticated users to the Filament app panel.
// Required by Socialstream and Fortify redirects.
Route::middleware(['auth'])->get('/dashboard', function () {
    return redirect()->route('filament.app.pages.dashboard');
})->name('dashboard');

// Guest work order routes
Route::get('/submit-request', [App\Http\Controllers\GuestWorkOrderController::class, 'create'])->name('guest.work-order.create');
Route::post('/submit-request', [App\Http\Controllers\GuestWorkOrderController::class, 'store'])->middleware('throttle:10,1')->name('guest.work-order.store');
Route::get('/request-submitted', [App\Http\Controllers\GuestWorkOrderController::class, 'success'])->name('guest.work-order.success');

require __DIR__.'/socialstream.php';