<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KlienController;
use App\Http\Controllers\Admin\LeadsController;
use App\Http\Controllers\Admin\LisensiController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\PengumumanController;
use App\Http\Controllers\Admin\RilisController;
use App\Http\Controllers\Admin\TelemetriController;
use App\Http\Controllers\Admin\TiketController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes: AksaraEdu Central Hub
|--------------------------------------------------------------------------
*/

// 1. Public Zone
Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/pricing', [PublicController::class, 'pricing'])->name('pricing');
Route::get('/verify', [PublicController::class, 'verify'])->name('verify');
Route::get('/demo', [PublicController::class, 'demo'])->name('demo');

// 2. Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 3. Vendor Master Control Panel (Protected by Auth)
Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Manajemen Klien Sekolah
    Route::get('/klien', [KlienController::class, 'index'])->name('klien.index');
    Route::post('/klien', [KlienController::class, 'store'])->name('klien.store');
    Route::get('/klien/{id}', [KlienController::class, 'show'])->name('klien.show');
    Route::put('/klien/{id}', [KlienController::class, 'update'])->name('klien.update');
    Route::delete('/klien/{id}', [KlienController::class, 'destroy'])->name('klien.destroy');

    // Master Licensing Engine
    Route::get('/lisensi', [LisensiController::class, 'index'])->name('lisensi.index');
    Route::post('/lisensi', [LisensiController::class, 'store'])->name('lisensi.store');
    Route::post('/lisensi/{id}/renew', [LisensiController::class, 'renew'])->name('lisensi.renew');
    Route::post('/lisensi/{id}/reset-hardware', [LisensiController::class, 'resetHardware'])->name('lisensi.reset-hardware');
    Route::post('/lisensi/{id}/revoke', [LisensiController::class, 'revoke'])->name('lisensi.revoke');
    Route::get('/lisensi/{id}/download', [LisensiController::class, 'downloadLicenseFile'])->name('lisensi.download');

    // Telemetri & Heartbeat Monitor
    Route::get('/telemetri', [TelemetriController::class, 'index'])->name('telemetri.index');

    // Release Repository Manager
    Route::get('/rilis', [RilisController::class, 'index'])->name('rilis.index');
    Route::post('/rilis', [RilisController::class, 'store'])->name('rilis.store');
    Route::get('/rilis/{id}/download', [RilisController::class, 'download'])->name('rilis.download');
    Route::delete('/rilis/{id}', [RilisController::class, 'destroy'])->name('rilis.destroy');

    // Support Helpdesk & SLA Tracker
    Route::get('/tiket', [TiketController::class, 'index'])->name('tiket.index');
    Route::post('/tiket', [TiketController::class, 'store'])->name('tiket.store');
    Route::patch('/tiket/{id}/status', [TiketController::class, 'updateStatus'])->name('tiket.status');

    // Leads & Demo CRM Pipeline
    Route::get('/leads', [LeadsController::class, 'index'])->name('leads.index');
    Route::patch('/leads/{id}/status', [LeadsController::class, 'updateStatus'])->name('leads.status');

    // Remote Broadcast / Announcement
    Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('pengumuman.index');
    Route::post('/pengumuman', [PengumumanController::class, 'store'])->name('pengumuman.store');
    Route::patch('/pengumuman/{id}/toggle', [PengumumanController::class, 'toggle'])->name('pengumuman.toggle');
    Route::delete('/pengumuman/{id}', [PengumumanController::class, 'destroy'])->name('pengumuman.destroy');

    // Manajemen Pengguna & Tim Vendor (RBAC Super Admin)
    Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
    Route::post('/pengguna', [PenggunaController::class, 'store'])->name('pengguna.store');
    Route::put('/pengguna/{id}', [PenggunaController::class, 'update'])->name('pengguna.update');
    Route::delete('/pengguna/{id}', [PenggunaController::class, 'destroy'])->name('pengguna.destroy');
});
