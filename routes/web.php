<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

// Dashboard Landing Page Router
Route::get('/', [TaskController::class, 'index'])->name('tasks.index');

// Create Task Handler Route
Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');

// Single-Click Status Switch Router
Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggleStatus'])->name('tasks.toggle');

// Permanent Deletion Route Handler
Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');