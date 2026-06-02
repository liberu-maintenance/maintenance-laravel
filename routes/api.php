<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IotSensorController;
use App\Http\Controllers\Api\V1\EquipmentController;
use App\Http\Controllers\Api\V1\WorkOrderController;
use App\Http\Controllers\Api\V1\MaintenanceScheduleController;
use App\Http\Controllers\Api\V1\InventoryPartController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\ChecklistController;
use App\Http\Controllers\Api\V1\NoteController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\OpportunityController;

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

// V1 API Routes — all protected by auth:sanctum and rate-limited via the 'api' throttle
Route::prefix('v1')
    ->middleware(['auth:sanctum', 'throttle:api'])
    ->group(function () {
        Route::apiResource('equipment', EquipmentController::class);
        Route::apiResource('work-orders', WorkOrderController::class);
        Route::apiResource('maintenance-schedules', MaintenanceScheduleController::class);
        Route::apiResource('inventory-parts', InventoryPartController::class);
        Route::apiResource('documents', DocumentController::class);
        Route::apiResource('contacts', ContactController::class);
        Route::apiResource('companies', CompanyController::class);
        Route::apiResource('checklists', ChecklistController::class);
        Route::apiResource('notes', NoteController::class);
        Route::apiResource('tasks', TaskController::class);
        Route::apiResource('opportunities', OpportunityController::class);
    });
