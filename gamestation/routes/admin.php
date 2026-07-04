<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\GenreController;
use App\Http\Controllers\Admin\ESRBController;
use App\Http\Controllers\Admin\PublisherController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\StoreInfoController;

// Admin Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post');

// Protected Admin Routes
Route::middleware('admin')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    
    // Products
    Route::resource('products', ProductController::class, ['as' => 'admin']);
    Route::delete('products/{product}/images', [ProductController::class, 'deleteAllImages'])
        ->name('admin.products.images.destroy-all');
    Route::delete('products/{product}/images/{image}', [ProductController::class, 'deleteImage'])
        ->name('admin.products.images.destroy');
    
    // Categories
    Route::resource('categories', CategoryController::class, ['as' => 'admin']);
    
    // Genres
    Route::resource('genres', GenreController::class, ['as' => 'admin']);
    
    // ESRB
    Route::resource('esrb', ESRBController::class, ['as' => 'admin']);

    // Publishers
    Route::resource('publishers', PublisherController::class, ['as' => 'admin']);
    
    // Orders
    Route::get('orders', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
    Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.updateStatus');
    
    // Customers
    Route::get('customers', [CustomerController::class, 'index'])->name('admin.customers.index');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('admin.customers.show');
    Route::post('customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('admin.customers.toggleStatus');
    
    // Banners
    Route::get('banners', [BannerController::class, 'index'])->name('admin.banners.index');
    Route::get('banners/create', [BannerController::class, 'create'])->name('admin.banners.create');
    Route::get('banners/{banner}/edit', [BannerController::class, 'edit'])->name('admin.banners.edit');
    Route::post('banners', [BannerController::class, 'store'])->name('admin.banners.store');
    Route::put('banners/{banner}', [BannerController::class, 'update'])->name('admin.banners.update');
    Route::put('banners/{banner}/order', [BannerController::class, 'updateOrder'])->name('admin.banners.updateOrder');
    Route::delete('banners/{banner}', [BannerController::class, 'destroy'])->name('admin.banners.destroy');
    
    // Articles
    Route::resource('articles', ArticleController::class, ['as' => 'admin']);
    
    // Reviews
    Route::get('reviews', [ReviewController::class, 'index'])->name('admin.reviews.index');
    Route::get('reviews/{review}', [ReviewController::class, 'show'])->name('admin.reviews.show');
    Route::post('reviews/{review}/reply', [ReviewController::class, 'reply'])->name('admin.reviews.reply');
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('admin.reviews.destroy');
    
    // Contacts
    Route::get('contacts', [ContactController::class, 'index'])->name('admin.contacts.index');
    Route::get('contacts/{contact}', [ContactController::class, 'show'])->name('admin.contacts.show');
    Route::post('contacts/{contact}/reply', [ContactController::class, 'reply'])->name('admin.contacts.reply');
    Route::delete('contacts/{contact}', [ContactController::class, 'destroy'])->name('admin.contacts.destroy');

    // Coupons
    Route::resource('coupons', CouponController::class, ['as' => 'admin']);

    // Store Info Settings
    Route::get('store-info', [StoreInfoController::class, 'edit'])->name('admin.store-info.edit');
    Route::post('store-info', [StoreInfoController::class, 'update'])->name('admin.store-info.update');
    
    // Statistics
    Route::get('statistics/revenue', [StatisticsController::class, 'revenue'])->name('admin.statistics.revenue');
    Route::get('statistics/orders', [StatisticsController::class, 'orders'])->name('admin.statistics.orders');
    Route::get('statistics/users', [StatisticsController::class, 'users'])->name('admin.statistics.users');
});