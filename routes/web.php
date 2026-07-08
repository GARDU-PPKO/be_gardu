<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AdminDusunController;
use App\Http\Controllers\Admin\AdminTourPackageController;
use App\Http\Controllers\Admin\AdminBookingSessionController;
use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminUmkmProductController;
use App\Http\Controllers\Admin\AdminBudayaController;
use App\Http\Controllers\Admin\AdminVillageProfileController;
use App\Http\Controllers\Admin\AdminVillageStatsController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\FonnteController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('admin.login')->middleware('guest');
    Route::post('login', [AuthController::class, 'login'])->name('admin.login.post')->middleware('guest');

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

        // Users (superadmin only)
        Route::resource('users', AdminUserController::class)->except(['show'])->names('admin.users');

        // Dusun
        Route::resource('dusun', AdminDusunController::class)->names('admin.dusun');
        Route::post('dusun/{id}/galleries', [AdminDusunController::class, 'storeGallery'])->name('admin.dusun.galleries.store');
        Route::delete('dusun/{id}/galleries/{galleryId}', [AdminDusunController::class, 'destroyGallery'])->name('admin.dusun.galleries.destroy');
        Route::post('dusun/{id}/keunggulan', [AdminDusunController::class, 'storeKeunggulan'])->name('admin.dusun.keunggulan.store');
        Route::delete('dusun/{id}/keunggulan/{keunggulanId}', [AdminDusunController::class, 'destroyKeunggulan'])->name('admin.dusun.keunggulan.destroy');

        // Tour Packages
        Route::resource('tour-packages', AdminTourPackageController::class)->names('admin.tour-packages');
        Route::post('tour-packages/{id}/includes', [AdminTourPackageController::class, 'storeInclude'])->name('admin.tour-packages.includes.store');
        Route::delete('tour-packages/{id}/includes/{includeId}', [AdminTourPackageController::class, 'destroyInclude'])->name('admin.tour-packages.includes.destroy');

        // Booking Sessions
        Route::get('booking-sessions', [AdminBookingSessionController::class, 'index'])->name('admin.booking-sessions.index');
        Route::get('booking-sessions/create', [AdminBookingSessionController::class, 'create'])->name('admin.booking-sessions.create');
        Route::post('booking-sessions', [AdminBookingSessionController::class, 'store'])->name('admin.booking-sessions.store');
        Route::get('booking-sessions/{id}/edit', [AdminBookingSessionController::class, 'edit'])->name('admin.booking-sessions.edit');
        Route::put('booking-sessions/{id}', [AdminBookingSessionController::class, 'update'])->name('admin.booking-sessions.update');
        Route::delete('booking-sessions/{id}', [AdminBookingSessionController::class, 'destroy'])->name('admin.booking-sessions.destroy');

        // Bookings
        Route::get('bookings', [AdminBookingController::class, 'index'])->name('admin.bookings.index');
        Route::get('bookings/export', [AdminBookingController::class, 'export'])->name('admin.bookings.export');
        Route::get('bookings/parse', [AdminBookingController::class, 'parse'])->name('admin.bookings.parse');
        Route::post('bookings/parse-text', [AdminBookingController::class, 'parseText'])->name('admin.bookings.parse-text');
        Route::get('bookings/{id}', [AdminBookingController::class, 'show'])->name('admin.bookings.show');
        Route::post('bookings/{id}/confirm', [AdminBookingController::class, 'confirm'])->name('admin.bookings.confirm');
        Route::delete('bookings/{id}', [AdminBookingController::class, 'destroy'])->name('admin.bookings.destroy');

        // UMKM Products
        Route::resource('umkm-products', AdminUmkmProductController::class)->except(['show'])->names('admin.umkm-products');

        // Budaya
        Route::resource('budaya', AdminBudayaController::class)->except(['show'])->names('admin.budaya');
        Route::post('budaya/{id}/schedules', [AdminBudayaController::class, 'storeSchedule'])->name('admin.budaya.schedules.store');
        Route::delete('budaya/{id}/schedules/{scheduleId}', [AdminBudayaController::class, 'destroySchedule'])->name('admin.budaya.schedules.destroy');

        // Village Profile
        Route::resource('village-profile', AdminVillageProfileController::class)->except(['show'])->names('admin.village-profile');

        // Village Stats
        Route::resource('village-stats', AdminVillageStatsController::class)->except(['show'])->names('admin.village-stats');

        // Settings (only index, edit, update)
        Route::get('settings', [AdminSettingController::class, 'index'])->name('admin.settings.index');
        Route::get('settings/{id}/edit', [AdminSettingController::class, 'edit'])->name('admin.settings.edit');
        Route::put('settings/{id}', [AdminSettingController::class, 'update'])->name('admin.settings.update');

        // Fonnte Device Info
        Route::get('fonnte-device', [FonnteController::class, 'device'])->name('admin.fonnte.device');
    });
});
