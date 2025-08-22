<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;

// Redirect root langsung ke projects.index
Route::get('/', [ProjectController::class, 'index'])->name('projects.index');

Route::resource('projects', ProjectController::class);
Route::resource('tasks', TaskController::class);


Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');