<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login');

Route::get('/', fn () => Inertia::render('Login'))->name('home');
Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');
Route::get('/projects', fn () => Inertia::render('Projects/Index'))->name('projects.index');
Route::get('/projects/{project}', fn () => Inertia::render('Projects/Show'))->name('projects.show');
Route::get('/tasks', fn () => Inertia::render('Tasks/Index'))->name('tasks.index');
