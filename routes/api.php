<?php

use App\Http\Controllers\Api\DeployWebhookController;
use App\Http\Controllers\Api\LeadApiController;
use App\Http\Controllers\Api\LicenseApiController;
use App\Http\Controllers\Api\UpdateApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes: AksaraEdu Central Hub Gateway
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // 1. Aktivasi Lisensi (Protected by throttle 30/min)
    Route::post('/license/activate', [LicenseApiController::class, 'activate'])
        ->middleware('throttle:30,1');

    // 2. Heartbeat & Telemetri Klien (Protected by throttle 60/min)
    Route::post('/license/heartbeat', [LicenseApiController::class, 'heartbeat'])
        ->middleware('throttle:60,1');

    // 3. Verifikasi Lisensi NPSN Publik (Protected by throttle 60/min)
    Route::get('/license/verify/{npsn}', [LicenseApiController::class, 'verifyNpsn'])
        ->middleware('throttle:60,1');

    // 4. Update Registry & Patch Downloader
    Route::get('/updates/check', [UpdateApiController::class, 'check'])
        ->middleware('throttle:60,1');
    Route::get('/updates/download/{version}', [UpdateApiController::class, 'download'])
        ->middleware('throttle:30,1');

    // 5. Leads & Instant Demo Generator
    Route::post('/leads/demo', [LeadApiController::class, 'store'])
        ->middleware('throttle:20,1');
});

// 6. Post-Deploy Webhook Trigger (Auto-Extract, Migrate, & Cache Optimization)
Route::post('/deploy-webhook', [DeployWebhookController::class, 'handle'])
    ->middleware('throttle:10,1');
