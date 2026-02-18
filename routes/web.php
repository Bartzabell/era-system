<?php

use App\Http\Controllers\CitizenController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidentReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('auth/Login', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('incident-report', [IncidentReportController::class, 'index'])->name('incident-report.index');
    Route::get('incident-report/create', [IncidentReportController::class, 'create'])->name('incident-report.create');
    Route::post('incident-report', [IncidentReportController::class, 'store'])->name('incident-report.store');
    Route::get('incident-report/{incidentReport}', [IncidentReportController::class, 'show'])->name('incident-report.show');
    Route::get('incident-report/{incidentReport}/edit', [IncidentReportController::class, 'edit'])->name('incident-report.edit');
    Route::put('incident-report/{incidentReport}', [IncidentReportController::class, 'update'])->name('incident-report.update');
    Route::delete('incident-report/{incidentReport}', [IncidentReportController::class, 'destroy'])->name('incident-report.destroy');
    Route::get('citizen-page', [CitizenController::class, 'index'])->name('citizen-page.index');

    Route::middleware('permission:admin_access|responder_access')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/export/monthly-report', [DashboardController::class, 'exportMonthlyReport'])->name('dashboard.export.monthly');
        Route::get('/dashboard/export/citizen-report', [DashboardController::class, 'exportCitizenReport'])->name('dashboard.export.citizen');

    });

    Route::middleware('permission:admin_access')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    });
});

require __DIR__.'/settings.php';
