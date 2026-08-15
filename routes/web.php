<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('welcome');

// Provider public profile
Route::get('/providers/{provider}', [App\Http\Controllers\ProviderController::class, 'show'])->name('providers.show');

// Car selection (guest-friendly — kept from existing system)
Route::post('/select-car', [App\Http\Controllers\ServiceController::class, 'selectCar'])->name('select-car');

// Public APIs
Route::post('/api/providers/nearby', [App\Http\Controllers\ProviderController::class, 'nearby'])->name('api.providers.nearby');
Route::get('/api/car-models', [App\Http\Controllers\ServiceController::class, 'getCarModels']);
Route::post('/api/calculate-price', [App\Http\Controllers\ServiceController::class, 'calculatePrice']);
Route::get('/api/timeslots', [App\Http\Controllers\TimeslotController::class, 'available'])->name('api.timeslots');

// Cart API
Route::post('/api/cart/add', [App\Http\Controllers\CartController::class, 'add'])->name('api.cart.add');
Route::post('/api/cart/remove', [App\Http\Controllers\CartController::class, 'remove'])->name('api.cart.remove');
Route::get('/api/cart/get', [App\Http\Controllers\CartController::class, 'get'])->name('api.cart.get');

// Auth
Auth::routes();

// Google Login
Route::get('auth/google', [App\Http\Controllers\Auth\GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [App\Http\Controllers\Auth\GoogleController::class, 'handleGoogleCallback']);

// Public service listing (for browsing — no provider context)
Route::get('/services', [App\Http\Controllers\ServiceController::class, 'publicServices'])->name('services.index');


Route::middleware(['auth'])->group(function () {

    // Dashboard (role-based redirect)
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', [App\Http\Controllers\DashboardController::class, 'index'])->name('home');

    // Profile
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // Payment callbacks (accessible to all auth users)
    Route::get('/payment/success', [App\Http\Controllers\PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/cancel', [App\Http\Controllers\PaymentController::class, 'cancel'])->name('payment.cancel');


    Route::middleware(['role:user,customer'])->group(function () {
        // Booking flow: view provider → book cart
        Route::get('/providers/{provider}/book-cart', [App\Http\Controllers\BookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [App\Http\Controllers\BookingController::class, 'store'])->name('bookings.store');
        Route::get('/bookings/{booking}/confirmation', [App\Http\Controllers\BookingController::class, 'confirmation'])->name('bookings.confirmation');
        Route::delete('/bookings/{booking}', [App\Http\Controllers\BookingController::class, 'cancel'])->name('bookings.cancel');
        Route::post('/bookings/{booking}/rate', [App\Http\Controllers\RatingController::class, 'store'])->name('bookings.rate');
    });


    Route::middleware(['role:provider'])->prefix('provider')->name('provider.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\ProviderDashboardController::class, 'index'])->name('dashboard');
        Route::post('/bookings/{booking}/assign', [App\Http\Controllers\ProviderDashboardController::class, 'assign'])->name('bookings.assign');
        Route::patch('/bookings/{booking}/status', [App\Http\Controllers\ProviderDashboardController::class, 'updateBookingStatus'])->name('bookings.status');

        // Workers management
        Route::resource('workers', App\Http\Controllers\WorkerController::class)->names([
            'index' => 'workers.index',
            'create' => 'workers.create',
            'store' => 'workers.store',
            'edit' => 'workers.edit',
            'update' => 'workers.update',
            'destroy' => 'workers.destroy',
        ]);

        // Service toggle
        Route::get('/services', [App\Http\Controllers\ProviderServiceController::class, 'index'])->name('services.index');
        Route::post('/services/{service}/toggle', [App\Http\Controllers\ProviderServiceController::class, 'toggle'])->name('services.toggle');
        Route::put('/services/hours', [App\Http\Controllers\ProviderServiceController::class, 'updateHours'])->name('services.hours');
    });

    // Worker status updates
    Route::patch('/bookings/{booking}/status', [App\Http\Controllers\BookingController::class, 'updateStatus'])->name('bookings.status');

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

        // Provider management
        Route::get('/providers', [App\Http\Controllers\AdminController::class, 'providers'])->name('providers.index');
        Route::get('/providers/create', [App\Http\Controllers\AdminController::class, 'createProvider'])->name('providers.create');
        Route::post('/providers', [App\Http\Controllers\AdminController::class, 'storeProvider'])->name('providers.store');
        Route::get('/providers/{provider}/edit', [App\Http\Controllers\AdminController::class, 'editProvider'])->name('providers.edit');
        Route::put('/providers/{provider}', [App\Http\Controllers\AdminController::class, 'updateProvider'])->name('providers.update');
        Route::delete('/providers/{provider}', [App\Http\Controllers\AdminController::class, 'destroyProvider'])->name('providers.destroy');

        // Global service catalog — explicit names to avoid admin.admin.* double prefix
        Route::resource('services', App\Http\Controllers\ServiceController::class)->names([
            'index' => 'admin.services.index',
            'create' => 'admin.services.create',
            'store' => 'admin.services.store',
            'show' => 'admin.services.show',
            'edit' => 'admin.services.edit',
            'update' => 'admin.services.update',
            'destroy' => 'admin.services.destroy',
        ]);

        // Time Duration management
        Route::post('durations', [App\Http\Controllers\ServiceController::class, 'storeDuration'])->name('admin.durations.store');
        Route::delete('durations/{duration}', [App\Http\Controllers\ServiceController::class, 'destroyDuration'])->name('admin.durations.destroy');

        // Service Category management
        Route::post('categories', [App\Http\Controllers\ServiceController::class, 'storeCategory'])->name('admin.categories.store');
        Route::delete('categories/{category}', [App\Http\Controllers\ServiceController::class, 'destroyCategory'])->name('admin.categories.destroy');

        // Car management (kept)
        Route::get('cars', [App\Http\Controllers\AdminCarController::class, 'index'])->name('cars.index');
        Route::post('cars/type', [App\Http\Controllers\AdminCarController::class, 'storeType'])->name('cars.storeType');
        Route::delete('cars/type/{type}', [App\Http\Controllers\AdminCarController::class, 'destroyType'])->name('cars.destroyType');
        Route::post('cars/model', [App\Http\Controllers\AdminCarController::class, 'storeModel'])->name('cars.storeModel');
        Route::delete('cars/model/{model}', [App\Http\Controllers\AdminCarController::class, 'destroyModel'])->name('cars.destroyModel');

        // Financial reports
        Route::get('/financial', [App\Http\Controllers\AdminController::class, 'financial'])->name('financial');
    });
});

