<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\WaliDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
});

Route::middleware(['auth', 'role:wali'])->prefix('wali')->name('wali.')->group(function (): void {
    Route::get('/', WaliDashboardController::class)->name('dashboard');
});
