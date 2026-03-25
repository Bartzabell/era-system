<?php

use App\Http\Controllers\AnnouncementAlertController;
use App\Http\Controllers\CitizenController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidentReportController;
use App\Http\Controllers\MonthlyReportController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('auth/Login', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('/email/verify/{id}/{hash}', function ($id, $hash) {
    $user = User::findOrFail($id);
    
    if (!hash_equals(sha1($user->email), $hash)) {
        abort(403);
    }
    
    $user->email_verified_at = Carbon::now();
    $user->save();
    
    return response('<h2 style="text-align:center; margin-top:50px;">✅ Email Verified! You may now go back to the app.</h2>');
})->middleware(['signed'])->name('verification.verify');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('incident-report', [IncidentReportController::class, 'index'])->name('incident-report.index');
    Route::post('incident-report', [IncidentReportController::class, 'store'])->name('incident-report.store');
    Route::put('incident-report/{incidentReport}', [IncidentReportController::class, 'update'])->name('incident-report.update');
    Route::delete('incident-report/{incidentReport}', [IncidentReportController::class, 'destroy'])->name('incident-report.destroy');
    Route::get('incident-report/{incidentReport}', [IncidentReportController::class, 'show'])->name('incident-report.show');

    Route::get('citizen-page', [CitizenController::class, 'index'])->name('citizen-page.index');

    // ── Notification routes — all authenticated roles need these ──────────────
    Route::get('/announcement-alert/notifications', [AnnouncementAlertController::class, 'notifications'])->name('announcement-alert.notifications');
    Route::post('/announcement-alert/mark-read', [AnnouncementAlertController::class, 'markAsRead'])->name('announcement-alert.mark-read');

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

        Route::get('/announcement-alert', [AnnouncementAlertController::class, 'index'])->name('announcement-alert.index');
        Route::post('/announcement-alert', [AnnouncementAlertController::class, 'store'])->name('announcement-alert.store');
        Route::delete('/announcement-alert/{announcementAlert}', [AnnouncementAlertController::class, 'destroy'])->name('announcement-alert.destroy');

        Route::get('/monthly-report',            [MonthlyReportController::class, 'index'])     ->name('monthly-report.index');
        Route::get('/monthly-report/export-csv', [MonthlyReportController::class, 'exportCsv'])->name('monthly-report.export-csv');
        Route::get('/monthly-report/export-pdf', [MonthlyReportController::class, 'exportPdf'])->name('monthly-report.export-pdf');
    });
});

require __DIR__.'/settings.php';
