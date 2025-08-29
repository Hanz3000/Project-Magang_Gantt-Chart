<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;

// Root diarahkan ke tasks.index
Route::get('/', [TaskController::class, 'index'])->name('tasks.index');

// Resource
Route::resource('projects', ProjectController::class);
Route::resource('tasks', TaskController::class);



Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');