<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AuthController;
// Pastikan Anda membuat controller ini untuk menangani logika PDF
// Misalnya, Anda bisa menamainya ExportController
// use App\Http\Controllers\ExportController;

Route::middleware('auth')->group(function () {
    // Dashboard default
    Route::get('/', [TaskController::class, 'index'])->name('tasks.index');

    // CRUD Project & Task
    Route::resource('projects', ProjectController::class);
    Route::resource('tasks', TaskController::class);

    // Task edit manual override (sebenarnya sudah termasuk di resource)
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // =======================================================
    // Rute Baru untuk Export PDF (Memperbaiki error 'Route not defined')
    // Anda harus menentukan controller dan method yang benar (misalnya: ExportController::class, 'exportGantt')
    // Saya menggunakan TaskController sebagai placeholder, ubah sesuai Controller Export Anda.
    // -------------------------------------------------------

    // 1. Export Seluruh Gantt Chart dan List
    Route::get('/gantt/export/pdf', [TaskController::class, 'exportGanttPdf'])
         ->name('gantt.export.pdf');

    // 2. Export Detail Tugas Spesifik (Digunakan di Modal Footer)
    // Rute ini sesuai dengan path '/tasks/{id}/export-pdf' yang digunakan di JavaScript
    Route::get('/tasks/{task}/export-pdf', [TaskController::class, 'exportTaskPdf'])
         ->name('tasks.export.pdf');

    // =======================================================

    // Profil
    Route::get('/profil', function () {
        return view('profil', ['user' => Auth::user()]);
    })->name('profil');

    // Ganti password
    Route::post('/profil/password', function (Request $request) {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        // Logout dulu agar user harus login dengan password baru
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Kata sandi berhasil diperbarui. Silakan login kembali.');
    })->name('profil.password.update');
});

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');