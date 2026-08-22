<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;


// Authentication routes
Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/tasksCreate', [TaskController::class, 'store']);
    Route::get('/tasksGet', [TaskController::class, 'viewTasks']);
    Route::put('/tasksUpdate/{taskId}', [TaskController::class, 'update']);
    Route::delete('/tasksDelete/{taskId}', [TaskController::class, 'destroy']);
    Route::get('/singleTasksGet/{taskId}', [TaskController::class, 'show']);
});


