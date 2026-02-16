<?php

use App\Http\Controllers\Api\AccountApiController;
use App\Http\Controllers\Api\HotlineApiController;
use App\Http\Controllers\Api\IncidentReportApiController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

if (!function_exists('validateApiToken')) {
    function validateApiToken(Request $request) {
        $token = $request->header('X-Mobile-Token');
        if ($token !== env('MOBILE_APP_TOKEN')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid token.',
            ], 401);
        }
        return null;
    }
}

Route::middleware('throttle:60,1')->group(function () {
    // Public routes
    Route::post('/auth/register', function (Request $request) {
        $authCheck = validateApiToken($request);
        if ($authCheck) return $authCheck;
        return app(AccountApiController::class)->register($request);
    });

    Route::post('/auth/login', function (Request $request) {
        $authCheck = validateApiToken($request);
        if ($authCheck) return $authCheck;
        return app(AccountApiController::class)->login($request);
    });

    // Protected routes (requires auth token)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/users/me', function (Request $request) {
            $authCheck = validateApiToken($request);
            if ($authCheck) return $authCheck;
            return app(AccountApiController::class)->getAccount($request);
        });

        // Add these new routes
        Route::put('/users/profile', function (Request $request) {
            $authCheck = validateApiToken($request);
            if ($authCheck) return $authCheck;
            return app(AccountApiController::class)->updateProfile($request);
        });

        Route::put('/users/password', function (Request $request) {
            $authCheck = validateApiToken($request);
            if ($authCheck) return $authCheck;
            return app(AccountApiController::class)->changePassword($request);
        });

        Route::get('/dropdowns', function (Request $request) {
            $authCheck = validateApiToken($request);
            if ($authCheck) return $authCheck;
            return app(IncidentReportApiController::class)->getDropdowns();
        });

        // Create incident report
        Route::post('/incident-reports', function (Request $request) {
            $authCheck = validateApiToken($request);
            if ($authCheck) return $authCheck;
            return app(IncidentReportApiController::class)->store($request);
        });

        // Get user's incident reports
        Route::get('/incident-reports', function (Request $request) {
            $authCheck = validateApiToken($request);
            if ($authCheck) return $authCheck;
            return app(IncidentReportApiController::class)->index($request);
        });

        Route::get('/hotlines', function (Request $request) {
            $authCheck = validateApiToken($request);
            if ($authCheck) return $authCheck;
            return app(HotlineApiController::class)->index();
        });

        // Get all pending reports (waiting status)
        Route::get('/incident-reports/pending', function (Request $request) {
            $authCheck = validateApiToken($request);
            if ($authCheck) return $authCheck;
            return app(IncidentReportApiController::class)->getPendingReports($request);
        });

        Route::get('/incident-reports/my-cases', function (Request $request) {
            $authCheck = validateApiToken($request);
            if ($authCheck) return $authCheck;
            return app(IncidentReportApiController::class)->getResponderCases($request);
        });

        // Accept a report
        Route::post('/incident-reports/{id}/accept', function (Request $request, $id) {
            $authCheck = validateApiToken($request);
            if ($authCheck) return $authCheck;
            return app(IncidentReportApiController::class)->acceptReport($request, $id);
        });

        // Update responder location (cache only)
        Route::post('/incident-reports/{id}/update-location', function (Request $request, $id) {
            $authCheck = validateApiToken($request);
            if ($authCheck) return $authCheck;
            return app(IncidentReportApiController::class)->updateResponderLocation($request, $id);
        });

        // Get responder location (from cache)
        Route::get('/incident-reports/{id}/responder-location', function (Request $request, $id) {
            $authCheck = validateApiToken($request);
            if ($authCheck) return $authCheck;
            return app(IncidentReportApiController::class)->getResponderLocation($request, $id);
        });
    });
});