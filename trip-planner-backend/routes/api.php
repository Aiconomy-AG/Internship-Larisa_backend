<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/trips', [\App\Http\Controllers\TripController::class, 'index']);
Route::post('/trips', [\App\Http\Controllers\TripController::class, 'store']);
Route::put('/trips/{id}', [\App\Http\Controllers\TripController::class, 'update']);
Route::delete('/trips/{id}', [\App\Http\Controllers\TripController::class, 'destroy']);

Route::post('/trips/{tripId}/activities', [\App\Http\Controllers\TripController::class, 'addActivity']);
Route::put('/activities/{activityId}', [\App\Http\Controllers\TripController::class, 'updateActivity']);
Route::delete('/activities/{activityId}', [\App\Http\Controllers\TripController::class, 'deleteActivity']);
