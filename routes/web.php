<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    if (Auth::user()?->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});
Route::middleware(['auth'])->group(function () {
    Route::get('/mood', [App\Http\Controllers\MoodLogController::class, 'index'])->name('mood.index');
    Route::post('/mood', [App\Http\Controllers\MoodLogController::class, 'store'])->name('mood.store');
});
Route::middleware(['auth'])->group(function () {
    Route::get('/routines', [App\Http\Controllers\RoutineController::class, 'index'])->name('routines.index');
    Route::get('/routines/create', [App\Http\Controllers\RoutineController::class, 'create'])->name('routines.create');
    Route::post('/routines', [App\Http\Controllers\RoutineController::class, 'store'])->name('routines.store');
    Route::post('/routines/{id}/upvote', [App\Http\Controllers\RoutineController::class, 'upvote'])->name('routines.upvote');
});
require __DIR__.'/auth.php';
