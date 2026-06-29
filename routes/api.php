<?php

use App\Http\Controllers\Public\BudayaController;
use App\Http\Controllers\Public\BookingSessionController;
use App\Http\Controllers\Public\DusunController;
use App\Http\Controllers\Public\SettingController;
use App\Http\Controllers\Public\TourPackageController;
use App\Http\Controllers\Public\UmkmProductController;
use App\Http\Controllers\Public\VillageProfileController;
use App\Http\Controllers\Public\VillageStatController;
use Illuminate\Support\Facades\Route;

Route::get('dusun', [DusunController::class, 'index']);
Route::get('dusun/{id}', [DusunController::class, 'show']);

Route::get('tour-packages', [TourPackageController::class, 'index']);
Route::get('tour-packages/{id}', [TourPackageController::class, 'show']);

Route::get('booking-sessions', [BookingSessionController::class, 'index']);

Route::get('umkm-products', [UmkmProductController::class, 'index']);

Route::get('budaya', [BudayaController::class, 'index']);
Route::get('budaya/{id}', [BudayaController::class, 'show']);

Route::get('village-profile', [VillageProfileController::class, 'index']);
Route::get('village-stats', [VillageStatController::class, 'index']);
Route::get('settings', [SettingController::class, 'index']);
