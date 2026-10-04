<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Application routes
|--------------------------------------------------------------------------
|
| Every screen is guarded by both the `role` middleware and a policy, so a
| user who types a URL they are not entitled to is refused server-side
| rather than merely being hidden from the menu.
|
*/

Route::middleware(['auth', 'role:admin,cashier'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Point of sale
        Route::prefix('pos')->name('pos.')->group(function () {
            Route::get('/', [PosController::class, 'index'])->name('index');
            Route::post('/cart', [PosController::class, 'updateCart'])->name('cart');
            Route::post('/context', [PosController::class, 'updateContext'])->name('context');
            Route::post('/orders', [PosController::class, 'store'])->name('store');
        });
    });

// Orders: all roles may read, staff drive the workflow.
Route::middleware(['auth'])
    ->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/advance', [OrderController::class, 'advance'])->name('orders.advance');
        Route::post('/orders/{order}/quality-control', [OrderController::class, 'storeQualityControl'])
            ->name('orders.quality-control');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

        Route::get('/tracking', [OrderTrackingController::class, 'index'])->name('tracking.index');

        Route::get('/orders/{order}/receipt', [ReceiptController::class, 'show'])->name('receipts.show');
    });

Route::middleware(['auth', 'role:admin,cashier'])
    ->group(function () {
        Route::resource('customers', CustomerController::class)->except(['destroy']);
        Route::post('customers/{customer}/deactivate', [CustomerController::class, 'destroy'])->name('customers.deactivate');
        Route::post('customers/{customer}/restore', [CustomerController::class, 'restore'])->name('customers.restore');

        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/orders/{order}/pay', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/orders/{order}/pay', [PaymentController::class, 'store'])->name('payments.store');
    });

Route::middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
        Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
        Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

        Route::get('/service-categories', [ServiceCategoryController::class, 'index'])->name('service-categories.index');
        Route::post('/service-categories', [ServiceCategoryController::class, 'store'])->name('service-categories.store');
        Route::put('/service-categories/{category}', [ServiceCategoryController::class, 'update'])->name('service-categories.update');
        Route::delete('/service-categories/{category}', [ServiceCategoryController::class, 'destroy'])->name('service-categories.destroy');

        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::put('/inventory/{inventory_item}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::delete('/inventory/{inventory_item}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('users', AdminUserController::class)->except(['show']);
    });

require __DIR__.'/auth.php';
