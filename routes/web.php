<?php

use App\Http\Controllers\Admin\DashboardController;

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AdminDusunController;
use App\Http\Controllers\Admin\AdminPaketWisataController;
use App\Http\Controllers\Admin\AdminAddOnController;
use App\Http\Controllers\Admin\AdminBookingSessionController;
use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminUmkmProductController;
use App\Http\Controllers\Admin\AdminBudayaController;
use App\Http\Controllers\Admin\AdminVillageStatsController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminPosController;
use App\Http\Controllers\Admin\FonnteController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('admin.login')->middleware('guest');
    Route::post('login', [AuthController::class, 'login'])->name('admin.login.post')->middleware('guest');

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

        // Kasir / POS Module
        Route::get('pos', [AdminPosController::class, 'index'])->name('admin.pos.index');
        Route::post('pos/checkout', [AdminPosController::class, 'storeTransaction'])->name('admin.pos.checkout');
        Route::get('pos/products', [AdminPosController::class, 'products'])->name('admin.pos.products.index');
        Route::post('pos/products', [AdminPosController::class, 'storeProduct'])->name('admin.pos.products.store');
        Route::put('pos/products/{id}', [AdminPosController::class, 'updateProduct'])->name('admin.pos.products.update');
        Route::delete('pos/products/{id}', [AdminPosController::class, 'destroyProduct'])->name('admin.pos.products.destroy');
        Route::get('pos/transactions', [AdminPosController::class, 'transactions'])->name('admin.pos.transactions.index');
        Route::get('pos/transactions/{id}/receipt', [AdminPosController::class, 'receipt'])->name('admin.pos.receipt');
        Route::post('pos/{id}/cancel', [AdminPosController::class, 'cancelTransaction'])->name('admin.pos.cancel');

        // Users (superadmin only)
        Route::resource('users', AdminUserController::class)->except(['show'])->names('admin.users');

        // Dusun
        Route::resource('dusun', AdminDusunController::class)->names('admin.dusun');
        Route::post('dusun/{id}/galleries', [AdminDusunController::class, 'storeGallery'])->name('admin.dusun.galleries.store');
        Route::delete('dusun/{id}/galleries/{galleryId}', [AdminDusunController::class, 'destroyGallery'])->name('admin.dusun.galleries.destroy');
        Route::post('dusun/{id}/keunggulan', [AdminDusunController::class, 'storeKeunggulan'])->name('admin.dusun.keunggulan.store');
        Route::delete('dusun/{id}/keunggulan/{keunggulanId}', [AdminDusunController::class, 'destroyKeunggulan'])->name('admin.dusun.keunggulan.destroy');

        // Paket Wisata
        Route::resource('paket-wisata', AdminPaketWisataController::class)->names('admin.paket-wisata');
        Route::post('paket-wisata/{id}/restore', [AdminPaketWisataController::class, 'restore'])->name('admin.paket-wisata.restore');
        Route::post('paket-wisata/{id}/tiers', [AdminPaketWisataController::class, 'storeTier'])->name('admin.paket-wisata.tiers.store');
        Route::delete('paket-wisata/{id}/tiers/{tierId}', [AdminPaketWisataController::class, 'destroyTier'])->name('admin.paket-wisata.tiers.destroy');

        // Add-On
        Route::resource('add-ons', AdminAddOnController::class)->except(['show'])->names('admin.add-ons');
        Route::post('add-ons/{id}/restore', [AdminAddOnController::class, 'restore'])->name('admin.add-ons.restore');

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
        Route::post('bookings/{id}/reject', [AdminBookingController::class, 'reject'])->name('admin.bookings.reject');
        Route::post('bookings/{id}/restore', [AdminBookingController::class, 'restore'])->name('admin.bookings.restore');
        Route::get('bookings/{id}/bukti', [AdminBookingController::class, 'showBukti'])->name('admin.bookings.bukti');
        Route::delete('bookings/{id}', [AdminBookingController::class, 'destroy'])->name('admin.bookings.destroy');

        // UMKM Products
        Route::resource('umkm-products', AdminUmkmProductController::class)->except(['show'])->names('admin.umkm-products');

        // Budaya
        Route::resource('budaya', AdminBudayaController::class)->except(['show'])->names('admin.budaya');
        Route::post('budaya/{id}/schedules', [AdminBudayaController::class, 'storeSchedule'])->name('admin.budaya.schedules.store');
        Route::delete('budaya/{id}/schedules/{scheduleId}', [AdminBudayaController::class, 'destroySchedule'])->name('admin.budaya.schedules.destroy');

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
Route::get('/orders/{order}/print-data', [OrderController::class, 'printData']);