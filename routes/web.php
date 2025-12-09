<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AuthController;

// --- Auth Routes ---
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- Protected Routes ---
Route::middleware('auth')->group(function () {
    
    // 1. Dashboard
    Route::get('/', [TaskController::class, 'index'])->name('tasks.index');

    // 2. Custom Task Routes (HARUS DI ATAS RESOURCE)
    // -----------------------------------------------------------
    
    // Update Progress Slider (Route yang Anda cari)
    Route::patch('/tasks/{task}/progress', [TaskController::class, 'updateProgress'])
         ->name('tasks.updateProgress');

    // Export PDF (Detail per Task)
    Route::get('/tasks/{task}/export-pdf', [TaskController::class, 'exportTaskPdf'])
         ->name('tasks.export.pdf');

    // Export Gantt Chart Global
    Route::get('/gantt/export/pdf', [TaskController::class, 'exportGanttPdf'])
         ->name('gantt.export.pdf');

    // 3. Resource Routes (Standard CRUD)
    // -----------------------------------------------------------
    // Resource ini otomatis membuat route: index, create, store, show, edit, update, destroy
    Route::resource('projects', ProjectController::class);
    Route::resource('tasks', TaskController::class);

    // 4. Profil Routes
    // -----------------------------------------------------------
    Route::get('/profil', function () {
        return view('profil', ['user' => Auth::user()]);
    })->name('profil');

    Route::post('/profil/password', function (Request $request) {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Kata sandi berhasil diperbarui. Silakan login kembali.');
    })->name('profil.password.update');
});