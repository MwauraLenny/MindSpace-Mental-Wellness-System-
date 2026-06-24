<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', [HealthController::class, 'liveness'])->name('health.liveness');
Route::get('/ready', [HealthController::class, 'readiness'])->name('health.readiness');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile/view', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
    Route::get('/analytics', [AnalyticsController::class, 'admin'])->name('admin.analytics');

    Route::get('/users', [UserManagementController::class, 'index'])->name('admin.users.index');
    Route::patch('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('admin.users.role.update');
    Route::get('/users/{user}/activity', [UserManagementController::class, 'activity'])->name('admin.users.activity');
    Route::patch('/users/{user}/suspend', [UserManagementController::class, 'suspend'])->name('admin.users.suspend');
    Route::patch('/users/{user}/unsuspend', [UserManagementController::class, 'unsuspend'])->name('admin.users.unsuspend');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/reports', [ReportController::class, 'admin'])->name('admin.reports.index');
    Route::get('/moderation', [ReportController::class, 'admin'])->name('admin.moderation.index');
    Route::get('/reports/export/csv', [ReportController::class, 'adminExportCsv'])->name('admin.reports.export.csv');
    Route::get('/reports/export/pdf', [ReportController::class, 'adminExportPdf'])->name('admin.reports.export.pdf');
    Route::patch('/reports/{report}/moderate', [ReportController::class, 'moderate'])->name('admin.reports.moderate');
});
Route::middleware(['auth'])->group(function () {
    Route::get('/analytics', [AnalyticsController::class, 'personal'])->name('analytics.personal');

    Route::get('/mood', [App\Http\Controllers\MoodLogController::class, 'index'])->name('mood.index');
    Route::post('/mood', [App\Http\Controllers\MoodLogController::class, 'store'])->name('mood.store');
    Route::get('/mood/dashboard', [App\Http\Controllers\MoodLogController::class, 'dashboard'])->name('mood.dashboard');
    Route::get('/mood/export/csv', [App\Http\Controllers\MoodLogController::class, 'exportCsv'])->name('mood.export.csv');
    Route::get('/mood/export/pdf', [App\Http\Controllers\MoodLogController::class, 'exportPdf'])->name('mood.export.pdf');
});
Route::middleware(['auth'])->group(function () {
    Route::get('/community', [App\Http\Controllers\RoutineController::class, 'feed'])->name('community.feed');
    Route::get('/routines', [App\Http\Controllers\RoutineController::class, 'index'])->name('routines.index');
    Route::get('/routines/saved', [App\Http\Controllers\RoutineController::class, 'saved'])->name('routines.saved');
    Route::get('/recommendations', [App\Http\Controllers\RoutineController::class, 'recommendations'])->name('routines.recommendations');
    Route::get('/routines/create', [App\Http\Controllers\RoutineController::class, 'create'])->name('routines.create');
    Route::post('/routines', [App\Http\Controllers\RoutineController::class, 'store'])->name('routines.store');
    Route::post('/routines/{id}/upvote', [App\Http\Controllers\RoutineController::class, 'upvote'])->name('routines.upvote');
    Route::post('/routines/{id}/save', [App\Http\Controllers\RoutineController::class, 'save'])->name('routines.save');
    Route::post('/routines/{id}/react', [App\Http\Controllers\RoutineController::class, 'react'])->name('routines.react');
    Route::post('/routines/{id}/comments', [App\Http\Controllers\RoutineController::class, 'comment'])->name('routines.comments.store');
    Route::delete('/routines/{id}/comments/{commentId}', [App\Http\Controllers\RoutineController::class, 'destroyComment'])->name('routines.comments.destroy');
});
Route::middleware(['auth'])->group(function () {
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');

    Route::get('/reports/personal', [ReportController::class, 'personal'])->name('reports.personal');
    Route::get('/reports/personal/export/csv', [ReportController::class, 'personalExportCsv'])->name('reports.personal.export.csv');
    Route::get('/reports/personal/export/pdf', [ReportController::class, 'personalExportPdf'])->name('reports.personal.export.pdf');
});
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});
Route::middleware(['auth'])->group(function () {
    Route::get('/journals', [JournalController::class, 'index'])->name('journals.index');
    Route::get('/journals/create', [JournalController::class, 'create'])->name('journals.create');
    Route::post('/journals', [JournalController::class, 'store'])->name('journals.store');
    Route::get('/journals/{id}', [JournalController::class, 'show'])->whereNumber('id')->name('journals.show');
    Route::get('/journals/{id}/edit', [JournalController::class, 'edit'])->name('journals.edit');
    Route::patch('/journals/{id}', [JournalController::class, 'update'])->name('journals.update');
    Route::delete('/journals/{id}', [JournalController::class, 'destroy'])->name('journals.destroy');
});
require __DIR__.'/auth.php';
