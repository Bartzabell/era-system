<?php

use App\Http\Controllers\CitizenController;
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
    Route::get('incident-report', [IncidentReportController::class, 'index'])->name('incident-report.index');
    Route::get('citizen-page', [CitizenController::class, 'index'])->name('citizen-page.index');

    Route::middleware('permission:admin_access|responder_access')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
});

require __DIR__.'/settings.php';
