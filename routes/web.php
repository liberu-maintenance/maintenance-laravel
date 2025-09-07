<?php

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

// Guest work order routes
Route::get('/submit-request', [App\Http\Controllers\GuestWorkOrderController::class, 'create'])->name('guest.work-order.create');
Route::post('/submit-request', [App\Http\Controllers\GuestWorkOrderController::class, 'store'])->name('guest.work-order.store');
Route::get('/request-submitted', [App\Http\Controllers\GuestWorkOrderController::class, 'success'])->name('guest.work-order.success');

require __DIR__.'/socialstream.php';