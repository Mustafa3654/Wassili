<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

// Public storefront (browse + client-side cart).
Route::get('/', [StorefrontController::class, 'index'])->name('storefront.index');

// Category detail page (all products).
Route::get('/category/{category}', [StorefrontController::class, 'category'])->name('storefront.category');

// AJAX checkout — creates a pending order and returns its tracking number.
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

// Public order tracking (no authentication required).
Route::get('/track/{tracking_number}', [TrackingController::class, 'show'])->name('track.show');

// Filament registers the login page as GET-only (Livewire handles POST internally).
// When Livewire JS is unavailable (bots, crawlers, proxy issues), a direct POST
// to /admin/login causes a 405. This fallback redirects such requests back to
// the actual login page so the browser fetches it via GET.
Route::post('/admin/login', function () {
    return redirect('/admin/login');
});
