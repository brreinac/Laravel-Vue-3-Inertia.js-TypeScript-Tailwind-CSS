<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function (): void {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard/stats', DashboardController::class);
    Route::get('/search', GlobalSearchController::class);
    Route::get('/users', [UserController::class, 'index'])->middleware('role:admin');

    Route::get('/tasks/export', [TaskController::class, 'export']);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'changeStatus']);
    Route::post('/tasks/{task}/comments', [CommentController::class, 'store']);
    Route::apiResource('tasks', TaskController::class);

    Route::get('/projects/{project}/tasks', [ProjectController::class, 'tasks']);
    Route::apiResource('projects', ProjectController::class);
});
