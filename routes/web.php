<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\AlertController;

// Update the home route to use the dashboard method
Route::get('/', [BillingController::class, 'dashboard'])->name('home');

// Billing system routes
Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
Route::get('/billing/create', [BillingController::class, 'create'])->name('billing.create');
Route::post('/billing', [BillingController::class, 'store'])->name('billing.store');
Route::get('/billing/today', [BillingController::class, 'todayBills'])->name('billing.today');

// Alerts routes
Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
Route::post('/alerts/{alert}/mark-read', [AlertController::class, 'markAsRead'])->name('alerts.mark-read');
