<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidentReportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('auth/Login', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('incident-report/index', [IncidentReportController::class, 'index'])->name('incident-report.index');
    // Admin Only Routes - Users Management
    Route::middleware('permission:admin_access')->group(function () {
    });
});

require __DIR__.'/settings.php';
