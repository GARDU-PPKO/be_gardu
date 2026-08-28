<?php

use App\Http\Controllers\Public\BookingController;
use App\Http\Controllers\Public\BudayaController;
use App\Http\Controllers\Public\BookingSessionController;
use App\Http\Controllers\Public\AddOnController;
use App\Http\Controllers\Public\DusunController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\SettingController;
use App\Http\Controllers\Public\TourPackageController;
use App\Http\Controllers\Public\UmkmProductController;
use App\Http\Controllers\Public\VillageStatController;
use App\Http\Controllers\Public\VisitorStatController;
use App\Http\Controllers\Api\FonnteWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('fonnte/webhook', FonnteWebhookController::class);

Route::get("test", function () {
    return "Hello Gardu!";
});

Route::get('home', [HomeController::class, 'index']);

Route::get('dusun', [DusunController::class, 'index']);
Route::get('dusun/{id}', [DusunController::class, 'show']);

Route::get('tour-packages', [TourPackageController::class, 'index']);
Route::get('tour-packages/{id}', [TourPackageController::class, 'show']);

Route::get('booking-sessions', [BookingSessionController::class, 'index']);

Route::get('addons', [AddOnController::class, 'index']);

Route::get('umkm-products', [UmkmProductController::class, 'index']);

Route::get('budaya', [BudayaController::class, 'index']);
Route::get('budaya/{id}', [BudayaController::class, 'show']);

Route::get('village-stats', [VillageStatController::class, 'index']);
Route::get('settings', [SettingController::class, 'index']);

Route::get('visitor-stats', [VisitorStatController::class, 'index']);
Route::post('visitor-stats/track', [VisitorStatController::class, 'track']);

// Public booking flow (tanpa login)
Route::get('bookings', [BookingController::class, 'history']);
Route::get('bookings/check', [BookingController::class, 'check']);
Route::post('bookings', [BookingController::class, 'store']);
Route::post('bookings/{booking_code}/bukti', [BookingController::class, 'uploadBukti']);
Route::get('bookings/{booking_code}/bukti', [BookingController::class, 'showBukti']);
Route::get('bookings/{booking_code}', [BookingController::class, 'show']);
Route::patch('bookings/{booking_code}', [BookingController::class, 'update']);
Route::patch('bookings/{booking_code}/cancel', [BookingController::class, 'cancel']);
Route::post('bookings/{booking_code}/resend-wa', [BookingController::class, 'resendWa']);
