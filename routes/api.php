<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\TripController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/trips', [TripController::class, 'index']);
    Route::post('/trips', [TripController::class, 'store']);
    Route::put('/trips/{id}', [TripController::class, 'update']);
    Route::delete('/trips/{id}', [TripController::class, 'destroy']);

    Route::get('/hotels/search', [HotelController::class, 'search']);

    Route::post('/trips/{tripId}/activities', [TripController::class, 'addActivity']);
    Route::put('/activities/{activityId}', [TripController::class, 'updateActivity']);
    Route::delete('/activities/{activityId}', [TripController::class, 'deleteActivity']);
});
