<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IotSensorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// IoT Sensor API Routes
Route::prefix('iot-sensors')->group(function () {
    // Public routes for sensor data submission (could be protected with API token)
    Route::post('/readings', [IotSensorController::class, 'storeReading']);
    Route::post('/readings/batch', [IotSensorController::class, 'storeBatchReadings']);
    
    // Protected routes for dashboard and analytics
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/dashboard', [IotSensorController::class, 'getDashboardData']);
        Route::get('/equipment/{equipment}/health', [IotSensorController::class, 'getEquipmentHealth']);
        Route::get('/equipment/{equipment}/insights', [IotSensorController::class, 'getPredictiveInsights']);
    });
});
