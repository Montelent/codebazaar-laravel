<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Support\Installer;
use Illuminate\Support\Facades\Route;

Route::prefix('install')->name('install.')->middleware('not.installed')->group(function () {
    Route::get('/', [InstallController::class, 'index'])->name('index');
    Route::get('/requirements', [InstallController::class, 'requirements'])->name('requirements');
    Route::get('/database', [InstallController::class, 'databaseForm'])->name('database');
    Route::post('/database', [InstallController::class, 'databaseStore'])->name('database.store');
    Route::get('/admin', [InstallController::class, 'adminForm'])->name('admin');
    Route::post('/finish', [InstallController::class, 'finish'])->name('finish');
});

Route::get('/setup', fn () => redirect()->route('install.index'));

if (! Installer::isInstalled()) {
    Route::get('/', fn () => redirect()->route('install.index'));
} else {
    Route::get('/', [HomeController::class, 'index'])->name('home');
}

Route::get('/search', [ItemController::class, 'search'])->name('search');
Route::get('/category/{slug}', [ItemController::class, 'category'])->name('category');
Route::get('/item/{slug}/{id}', [ItemController::class, 'show'])->name('item.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::delete('/cart/{key}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'place'])->name('checkout.place');
Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('index');
    Route::get('/purchases', [AccountController::class, 'purchases'])->name('purchases');
    Route::get('/downloads', [AccountController::class, 'downloads'])->name('downloads');
    Route::get('/download/{itemId}', [AccountController::class, 'downloadFile'])->name('download');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('products', ProductController::class)->except(['show']);
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
});
