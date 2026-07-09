<?php

use App\Http\Controllers\AnnouncementAlertController;
use App\Http\Controllers\CitizenController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\DutyLogController;
use App\Http\Controllers\IncidentReportController;
use App\Http\Controllers\MonthlyReportController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\RegisterController;
use App\Models\IncidentReport;
use App\Models\Responder;
use Illuminate\Http\Request;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LoginSettingController;
use App\Http\Controllers\PatientRecordChartController;
use App\Http\Controllers\SettingsController;
use App\Models\MobileBridgeToken;
use Illuminate\Support\Facades\Auth;

Route::get('/', [LoginController::class, 'create'])->name('home');
//Route::get('/login', [LoginController::class, 'create'])->name('login');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

Route::get('/email/verify/{id}/{hash}', function ($id, $hash) {
    $user = User::findOrFail($id);

    if (!hash_equals(sha1($user->email), $hash)) {
        abort(403);
    }

    $user->email_verified_at = Carbon::now();
    $user->save();

    return response('<h2 style="text-align:center; margin-top:50px;">✅ Email Verified! You may now go back to the app.</h2>');
})->middleware(['signed'])->name('verification.verify');

Route::get('/mobile-login-bridge', function (Illuminate\Http\Request $request) {
    $tokenValue = $request->query('token');

    if (!$tokenValue) {
        abort(403, 'Missing token');
    }

    $bridgeToken = MobileBridgeToken::where('token', $tokenValue)->first();

    if (!$bridgeToken || !$bridgeToken->isValid()) {
        abort(403, 'Invalid or expired token');
    }

    $bridgeToken->update(['used_at' => now()]);

    Auth::login($bridgeToken->user);

    return redirect('/patient-record-chart');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // Incident Reports - accessible by admin, assistant admin, responder
    Route::middleware('role:administrator,assistant admin,responder')->group(function () {
        Route::get('incident-report', [IncidentReportController::class, 'index'])->name('incident-report.index');
        Route::post('incident-report', [IncidentReportController::class, 'store'])->name('incident-report.store');
        Route::put('incident-report/{incidentReport}', [IncidentReportController::class, 'update'])->name('incident-report.update');
        Route::delete('incident-report/{incidentReport}', [IncidentReportController::class, 'destroy'])->name('incident-report.destroy');
        Route::get('incident-report/{incidentReport}/print', [IncidentReportController::class, 'print'])->name('incident-report.print');
    });

    // Dispatch - accessible by admin and assistant admin
    Route::middleware('role:administrator,assistant admin')->group(function () {
        Route::get('/dispatch', [DispatchController::class, 'index'])->name('dispatch.index');
        Route::post('/dispatch', [DispatchController::class, 'store'])->name('dispatch.store');
        Route::put('/dispatch/{incidentReport}', [DispatchController::class, 'update'])->name('dispatch.update');
        Route::delete('/dispatch/{incidentReport}', [DispatchController::class, 'destroy'])->name('dispatch.destroy');
        Route::post('/dispatch/{incidentReport}/assign-team', [DispatchController::class, 'assignTeam'])->name('dispatch.assignTeam');
    });

    Route::middleware('role:administrator,assistant admin,responder')->group(function () {
        Route::get    ('patient-record-chart',                            [PatientRecordChartController::class, 'index'])   ->name('patient-record-chart.index');
        Route::post   ('patient-record-chart',                            [PatientRecordChartController::class, 'store'])   ->name('patient-record-chart.store');
        Route::get    ('patient-record-chart/{patientRecordChart}/print', [PatientRecordChartController::class, 'print'])   ->name('patient-record-chart.print');  // ← ADD
        Route::put    ('patient-record-chart/{patientRecordChart}',       [PatientRecordChartController::class, 'update'])  ->name('patient-record-chart.update');
        Route::delete ('patient-record-chart/{patientRecordChart}',       [PatientRecordChartController::class, 'destroy']) ->name('patient-record-chart.destroy');
    });

    // Citizen page
    Route::get('citizen-page', [CitizenController::class, 'index'])->name('citizen-page.index');

    // Announcement notifications - accessible by admin and assistant admin
    Route::middleware('role:administrator,assistant admin')->group(function () {
        Route::get('/announcement-alert/notifications', [AnnouncementAlertController::class, 'notifications'])->name('announcement-alert.notifications');
        Route::post('/announcement-alert/mark-read', [AnnouncementAlertController::class, 'markAsRead'])->name('announcement-alert.mark-read');
    });

    // Dashboard - accessible by admin, assistant admin, responder
    Route::middleware('role:administrator,assistant admin,responder')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/export/monthly-report', [DashboardController::class, 'exportMonthlyReport'])->name('dashboard.export.monthly');
        Route::get('/dashboard/export/citizen-report', [DashboardController::class, 'exportCitizenReport'])->name('dashboard.export.citizen');

        Route::get('/api/responders', function () {
            return Responder::with('user')
                ->whereNull('deleted_at')
                ->whereHas('user.dutyLogs', function ($q) {
                    $q->whereDate('duty_date', today());
                })
                ->get()
                ->map(fn($r) => [
                    'id'              => $r->id,
                    'first_name'      => $r->user?->first_name ?? '',
                    'last_name'       => $r->user?->last_name  ?? '',
                    'contact_no'      => $r->user?->mobile_no  ?? null,
                    'profile_picture' => $r->user?->profile_picture ?? null,
                    'is_active'       => $r->is_active,
                ]);
        });

        Route::get('/api/incident-reports', function (Request $request) {
            return IncidentReport::with(['incident', 'barangay'])
                ->when($request->status, fn($q) => $q->where('status', $request->status))
                ->when($request->date,   fn($q) => $q->whereDate('updated_at', $request->date))
                ->whereNull('deleted_at')
                ->latest('updated_at')
                ->get()
                ->map(fn($r) => [
                    'id'             => $r->id,
                    'incident_code'  => $r->incident_code,
                    'incident_name'  => $r->incident->incident_name ?? 'Unknown',
                    'barangay_name'  => $r->barangay->barangay_name ?? 'Unknown',
                    'priority_level' => $r->priority_level,
                    'priority_label' => $r->priority_label,
                    'status'         => $r->status,
                    'updated_at'     => $r->updated_at,
                ]);
        });
    });

    // Admin only routes
    Route::middleware('role:administrator')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/duty-logs', [DutyLogController::class, 'index'])->name('duty-logs.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::post('/users/{user}/verify', [UserController::class, 'verify'])
            ->name('users.verify');

        Route::delete('/users/{user}/reject-verification', [UserController::class, 'rejectVerification'])
            ->name('users.rejectVerification');

        Route::get('/announcement-alert', [AnnouncementAlertController::class, 'index'])->name('announcement-alert.index');
        Route::post('/announcement-alert', [AnnouncementAlertController::class, 'store'])->name('announcement-alert.store');
        Route::delete('/announcement-alert/{announcementAlert}', [AnnouncementAlertController::class, 'destroy'])->name('announcement-alert.destroy');

        Route::get('/monthly-report',            [MonthlyReportController::class, 'index'])     ->name('monthly-report.index');
        Route::get('/monthly-report/export-csv', [MonthlyReportController::class, 'exportCsv'])->name('monthly-report.export-csv');
        Route::get('/monthly-report/export-pdf', [MonthlyReportController::class, 'exportPdf'])->name('monthly-report.export-pdf');

        Route::get('/login-settings',         [LoginSettingController::class, 'index'])       ->name('login-settings.index');
        Route::put('/login-settings/login',   [LoginSettingController::class, 'updateLogin']) ->name('login-settings.updateLogin');
        Route::put('/login-settings/terms',   [LoginSettingController::class, 'updateTerms']) ->name('login-settings.updateTerms');

        Route::get('/system-settings', [SettingsController::class, 'index'])->name('system-settings.index');

        // Hotline
        Route::post  ('/system-settings/hotline',          [SettingsController::class, 'storeHotline'])   ->name('system-settings.hotline.store');
        Route::put   ('/system-settings/hotline/{hotline}', [SettingsController::class, 'updateHotline']) ->name('system-settings.hotline.update');
        Route::delete('/system-settings/hotline/{hotline}', [SettingsController::class, 'destroyHotline'])->name('system-settings.hotline.destroy');

        // Emergency
        Route::post  ('/system-settings/emergency',              [SettingsController::class, 'storeEmergency'])   ->name('system-settings.emergency.store');
        Route::put   ('/system-settings/emergency/{emergency}',  [SettingsController::class, 'updateEmergency']) ->name('system-settings.emergency.update');
        Route::delete('/system-settings/emergency/{emergency}',  [SettingsController::class, 'destroyEmergency'])->name('system-settings.emergency.destroy');

        // Incident
        Route::post  ('/system-settings/incident',            [SettingsController::class, 'storeIncident'])   ->name('system-settings.incident.store');
        Route::put   ('/system-settings/incident/{incident}', [SettingsController::class, 'updateIncident']) ->name('system-settings.incident.update');
        Route::delete('/system-settings/incident/{incident}', [SettingsController::class, 'destroyIncident'])->name('system-settings.incident.destroy');

        Route::post  ('/system-settings/barangay',            [SettingsController::class, 'storeBarangay'])   ->name('system-settings.barangay.store');
        Route::put   ('/system-settings/barangay/{barangay}', [SettingsController::class, 'updateBarangay']) ->name('system-settings.barangay.update');
        Route::delete('/system-settings/barangay/{barangay}', [SettingsController::class, 'destroyBarangay'])->name('system-settings.barangay.destroy');
    });
});

require __DIR__.'/settings.php';
