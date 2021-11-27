<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Checkout\LinkController as CheckoutLinkController;
use App\Http\Controllers\Checkout\OrderController as CheckoutOrderController;
use App\Http\Controllers\Influencer\LinkController as InfluencerLinkController;
use App\Http\Controllers\Influencer\ProductController as InfluencerProductController;
use App\Http\Controllers\Influencer\StatsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Admin
Route::prefix('admin')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('register', [AuthController::class, 'register'])->name('register');

    Route::middleware(['auth:api', 'scope:admin'])->group(function () {
        Route::get('user', [AuthController::class, 'user'])->name('users.profile');
        Route::put('users/info', [AuthController::class, 'updateInfo'])->name('users.info');
        Route::put('users/password', [AuthController::class, 'updatePassword'])->name('users.update-password');

        Route::post('upload', [ImageController::class, 'upload'])->name('upload');
        Route::get('export', [OrderController::class, 'export'])->name('order-export');
        Route::get('chart', [DashboardController::class, 'chart'])->name('dashboard-chart');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/current-month', [ReportController::class, 'currentMonth'])->name('reports.current-month');
        Route::get('reports/last-quarter', [ReportController::class, 'lastQuarter'])->name('reports.last-quarter');
        Route::get('orders/latest', [OrderController::class, 'latestTransaction'])->name('orders.latest');

        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('products', ProductController::class);
        Route::apiResource('orders', OrderController::class)->only('index', 'show');
        Route::apiResource('permissions', PermissionController::class)->only('index');
    });
});

// Influencer
Route::prefix('influencer')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('register', [AuthController::class, 'register'])->name('register');

    Route::get('products', [InfluencerProductController::class, 'index']);

    Route::middleware(['auth:api', 'scope:influencer'])->group(function () {
        Route::get('user', [AuthController::class, 'user'])->name('users.profile');
        Route::put('users/info', [AuthController::class, 'updateInfo'])->name('users.info');
        Route::put('users/password', [AuthController::class, 'updatePassword'])->name('users.update-password');

        Route::post('links', [InfluencerLinkController::class, 'store']);
        Route::get('stats', [StatsController::class, 'index']);
        Route::get('rankings', [StatsController::class, 'rankings']);
    });
});

// Checkout
Route::group(['prefix' => 'checkout'], function () {
    Route::get('links/{code}', [CheckoutLinkController::class, 'show']);
    Route::post('orders', [CheckoutOrderController::class, 'store']);
    Route::post('orders/confirm', [CheckoutOrderController::class, 'confirm']);
});
