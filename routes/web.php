<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Admin\AdSettingsController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\BlogAdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HeaderFooterSettingsController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\NavigationSettingsController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\Admin\PageAdminController;
use App\Http\Controllers\Admin\PaymentSettingsController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SchemaSettingsController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SettingsHubController;
use App\Http\Controllers\Admin\SmtpSettingsController;
use App\Http\Controllers\Admin\StorageSettingsController;
use App\Http\Controllers\Admin\SystemToolsController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\UserAdminController;
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

Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SitemapController::class, 'xml'])->name('sitemap.xml');
Route::get('/sitemap', [SitemapController::class, 'html'])->name('sitemap.html');

Route::get('/search', [ItemController::class, 'search'])->name('search');
Route::get('/category/{slug}', [ItemController::class, 'category'])->name('category');
Route::get('/item/{slug}/{id}', [ItemController::class, 'show'])->name('item.show');
Route::get('/author/{username}', [AuthorController::class, 'show'])->name('author.show');
Route::get('/collection/{username}/{slug}', [CollectionController::class, 'show'])->name('collections.show');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');
Route::get('/pricing/licenses', [LicenseController::class, 'publicIndex'])->name('licenses.public');

Route::get('/media/file/{path}', [MediaController::class, 'serve'])->where('path', '.*')->name('media.serve');

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
Route::get('/email/verify/{id}', [AuthController::class, 'verify'])->name('verification.verify');

Route::middleware('auth')->group(function () {
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])->name('verification.send');
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::get('/purchases', [AccountController::class, 'purchases'])->name('purchases');
        Route::get('/downloads', [AccountController::class, 'downloads'])->name('downloads');
        Route::get('/download/{itemId}', [AccountController::class, 'downloadFile'])->name('download');
        Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
        Route::get('/collections', [CollectionController::class, 'index'])->name('collections');
    });
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/follow/{userId}', [FollowController::class, 'toggle'])->name('follow.toggle');
    Route::post('/collections', [CollectionController::class, 'store'])->name('collections.store');
    Route::post('/collections/add-item', [CollectionController::class, 'addItem'])->name('collections.add');
    Route::delete('/collections/{collection}', [CollectionController::class, 'destroy'])->name('collections.destroy');
    Route::delete('/collections/{collection}/items/{item}', [CollectionController::class, 'removeItem'])->name('collections.remove');
    Route::post('/item/{itemId}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/tools', [SystemToolsController::class, 'index'])->name('tools.index');
    Route::match(['get', 'post'], '/tools/run/{action}', [SystemToolsController::class, 'run'])
        ->where('action', 'migrate|clear-cache')
        ->name('tools.run');
    Route::post('/tools/migrate', [SystemToolsController::class, 'migrate'])->name('tools.migrate');
    Route::post('/tools/clear-cache', [SystemToolsController::class, 'clearCache'])->name('tools.clear-cache');
    Route::match(['get', 'post'], '/migrate', [SystemToolsController::class, 'migrate'])->name('migrate');

    Route::get('/products/category-attributes/{category}', [ProductController::class, 'categoryAttributes'])
        ->name('products.category-attributes');

    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes.index');
    Route::put('/attributes', [AttributeController::class, 'update'])->name('attributes.update');
    Route::post('/attributes/reset', [AttributeController::class, 'reset'])->name('attributes.reset');
    Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
    Route::put('/tags', [TagController::class, 'update'])->name('tags.update');
    Route::get('/media', [MediaController::class, 'index'])->name('media.index');
    Route::get('/media/json', [MediaController::class, 'json'])->name('media.json');
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('/media/{medium}', [MediaController::class, 'destroy'])->name('media.destroy');
    Route::get('/newsletter', [NewsletterController::class, 'index'])->name('newsletter.index');
    Route::post('/newsletter/send', [NewsletterController::class, 'send'])->name('newsletter.send');
    Route::resource('users', UserAdminController::class)->except(['show']);
    Route::resource('blog', BlogAdminController::class)->except(['show'])->parameters(['blog' => 'post']);
    Route::resource('pages', PageAdminController::class)->except(['show']);
    Route::resource('licenses', LicenseController::class)->except(['show']);
    Route::get('/orders', [OrderAdminController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderAdminController::class, 'show'])->name('orders.show');

    Route::get('/settings', [SettingsHubController::class, 'index'])->name('settings.hub');
    Route::get('/settings/general', [SettingController::class, 'edit'])->name('settings.general');
    Route::put('/settings/general', [SettingController::class, 'update'])->name('settings.general.update');
    Route::get('/settings/edit', fn () => redirect()->route('admin.settings.general'))->name('settings.edit');
    Route::put('/settings/update', [SettingController::class, 'update'])->name('settings.update');

    Route::get('/settings/payments', [PaymentSettingsController::class, 'edit'])->name('settings.payments');
    Route::put('/settings/payments', [PaymentSettingsController::class, 'update'])->name('settings.payments.update');
    Route::get('/settings/smtp', [SmtpSettingsController::class, 'edit'])->name('settings.smtp');
    Route::put('/settings/smtp', [SmtpSettingsController::class, 'update'])->name('settings.smtp.update');
    Route::post('/settings/smtp/test', [SmtpSettingsController::class, 'test'])->name('settings.smtp.test');
    Route::get('/settings/storage', [StorageSettingsController::class, 'edit'])->name('settings.storage');
    Route::put('/settings/storage', [StorageSettingsController::class, 'update'])->name('settings.storage.update');
    Route::get('/settings/navigation', [NavigationSettingsController::class, 'edit'])->name('settings.navigation');
    Route::put('/settings/navigation', [NavigationSettingsController::class, 'update'])->name('settings.navigation.update');
    Route::get('/settings/header-footer', [HeaderFooterSettingsController::class, 'edit'])->name('settings.header_footer');
    Route::put('/settings/header-footer', [HeaderFooterSettingsController::class, 'update'])->name('settings.header_footer.update');
    Route::get('/settings/ads', [AdSettingsController::class, 'edit'])->name('settings.ads');
    Route::put('/settings/ads', [AdSettingsController::class, 'update'])->name('settings.ads.update');
    Route::get('/settings/schema', [SchemaSettingsController::class, 'edit'])->name('settings.schema');
    Route::put('/settings/schema', [SchemaSettingsController::class, 'update'])->name('settings.schema.update');
});
