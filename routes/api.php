<?php

use App\Http\Controllers\Api\AccountApiController;
use App\Http\Controllers\Api\AnnouncementApiController;
use App\Http\Controllers\Api\HotlineApiController;
use App\Http\Controllers\Api\IncidentReportApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:60,1', 'mobile.token'])->group(function () {
    // Public routes
    Route::post('/auth/register', [AccountApiController::class, 'register']);
    Route::post('/auth/login', [AccountApiController::class, 'login']);
    Route::post('/auth/forgot-password', [AccountApiController::class, 'forgotPassword']);
    Route::post('/auth/verify-reset-token', [AccountApiController::class, 'verifyResetToken']);
    Route::post('/auth/reset-password', [AccountApiController::class, 'resetPassword']);
    Route::get('/dropdowns', [IncidentReportApiController::class, 'getDropdowns']);

    Route::post('/email/resend', [AccountApiController::class, 'resendVerification']);
    Route::post('/email/check', [AccountApiController::class, 'checkVerification']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // User
        Route::get('/users/me', [AccountApiController::class, 'getAccount']);
        Route::put('/users/profile', [AccountApiController::class, 'updateProfile']);
        Route::put('/users/password', [AccountApiController::class, 'changePassword']);
        Route::post('/users/profile-picture', [AccountApiController::class, 'updateProfilePicture']);
        Route::post('/users/verify-id', [AccountApiController::class, 'submitValidId']);
        Route::post('/mobile-bridge-token', [AccountApiController::class, 'generateBridgeToken']);

        Route::get('/hotlines', [HotlineApiController::class, 'index']);
        Route::post('/responder/update-facility', [IncidentReportApiController::class, 'updateResponderFacility']);

        // Incident Reports
        Route::post('/incident-reports', [IncidentReportApiController::class, 'store']);
        Route::get('/incident-reports', [IncidentReportApiController::class, 'index']);
        Route::get('/user-reports', [IncidentReportApiController::class, 'getUserReports']);
        Route::get('/incident-reports/pending', [IncidentReportApiController::class, 'getPendingReports']);
        Route::get('/incident-reports/my-cases', [IncidentReportApiController::class, 'getResponderCases']);
        Route::post('/incident-reports/{id}/accept', [IncidentReportApiController::class, 'acceptReport']);
        Route::put('/incident-reports/{id}', [IncidentReportApiController::class, 'updateReport']);
        Route::post('/incident-reports/{id}/cancel', [IncidentReportApiController::class, 'cancelReport']);
        Route::get('/incident-reports/{id}/attachments', [IncidentReportApiController::class, 'getAttachments']);
        Route::post('/incident-reports/{id}/attachments', [IncidentReportApiController::class, 'uploadAttachments']);
        Route::post('/incident-reports/{id}/responder-attachments', [IncidentReportApiController::class, 'uploadResponderAttachments']);
        Route::get('/site-locations', [IncidentReportApiController::class, 'getSiteLocations']);

        // Location tracking
        Route::post('/incident-reports/{id}/location', [IncidentReportApiController::class, 'updateResponderLocation']);
        Route::get('/incident-reports/{id}/location', [IncidentReportApiController::class, 'getResponderLocation']);

        // Announcements
        Route::get('/announcements', [AnnouncementApiController::class, 'index']);
        Route::post('/announcements/{id}/mark-as-read', [AnnouncementApiController::class, 'markAsRead']);

        Route::post('/auth/logout', [AccountApiController::class, 'logout']);
    });
});
