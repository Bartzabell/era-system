<?php

use App\Http\Controllers\Api\AccountApiController;
use App\Http\Controllers\Api\HotlineApiController;
use App\Http\Controllers\Api\IncidentReportApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:60,1', 'mobile.token'])->group(function () {
    // Public routes
    Route::post('/auth/register', [AccountApiController::class, 'register']);
    Route::post('/auth/login', [AccountApiController::class, 'login']);
    Route::get('/dropdowns', [IncidentReportApiController::class, 'getDropdowns']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // User
        Route::get('/users/me', [AccountApiController::class, 'getAccount']);
        Route::put('/users/profile', [AccountApiController::class, 'updateProfile']);
        Route::put('/users/password', [AccountApiController::class, 'changePassword']);

        Route::get('/hotlines', [HotlineApiController::class, 'index']);
        Route::post('/responder/update-facility', [IncidentReportApiController::class, 'updateResponderFacility']);

        // Incident Reports
        Route::post('/incident-reports', [IncidentReportApiController::class, 'store']);
        Route::get('/incident-reports', [IncidentReportApiController::class, 'index']);
        Route::get('/incident-reports/pending', [IncidentReportApiController::class, 'getPendingReports']);
        Route::get('/incident-reports/my-cases', [IncidentReportApiController::class, 'getResponderCases']);
        Route::post('/incident-reports/{id}/accept', [IncidentReportApiController::class, 'acceptReport']);
        Route::put('/incident-reports/{id}', [IncidentReportApiController::class, 'updateReport']);
        Route::post('/incident-reports/{id}/cancel', [IncidentReportApiController::class, 'cancelReport']);
        Route::get('/site-locations', [IncidentReportApiController::class, 'getSiteLocations']);
        
        // Location tracking
        Route::post('/incident-reports/{id}/location', [IncidentReportApiController::class, 'updateResponderLocation']);
        Route::get('/incident-reports/{id}/location', [IncidentReportApiController::class, 'getResponderLocation']);
    });
});