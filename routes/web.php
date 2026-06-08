<?php

use App\Http\Controllers\ScholarshipController;
use App\Http\Controllers\AdminScholarshipController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', [ScholarshipController::class, 'index'])->name('dashboard');
    Route::post('/calculate', [ScholarshipController::class, 'calculate'])->name('matchmaking.calculate');
    Route::post('/scholarship/{id}/go', [ScholarshipController::class, 'trackClick'])->name('matchmaking.track-click');
    Route::get('/scholarships', [ScholarshipController::class, 'catalog'])->name('scholarships.catalog');
    Route::get('/premium', [ScholarshipController::class, 'premium'])->name('premium.index');
    Route::post('/premium/upgrade', [ScholarshipController::class, 'upgradePremium'])->name('premium.upgrade');

    // Admin CRUD & Weight Config Routing
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/scholarships', [AdminScholarshipController::class, 'index'])->name('scholarships.index');
        Route::post('/scholarships', [AdminScholarshipController::class, 'store'])->name('scholarships.store');
        Route::put('/scholarships/{id}', [AdminScholarshipController::class, 'update'])->name('scholarships.update');
        Route::delete('/scholarships/{id}', [AdminScholarshipController::class, 'destroy'])->name('scholarships.destroy');
        Route::post('/weights', [AdminScholarshipController::class, 'updateWeights'])->name('weights.update');
        Route::post('/settings', [AdminScholarshipController::class, 'updateSettings'])->name('settings.update');
        Route::post('/users/purge', [AdminScholarshipController::class, 'purgeInactiveUsers'])->name('users.purge');
    });
});

