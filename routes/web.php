<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FaqController;

// API контроллеры
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\CartController as ApiCartController;

// Главная
Route::get('/', [HomeController::class, 'index'])->name('home');

// Категории и товары
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/category/{category}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/product/{product}', [ProductController::class, 'show'])->name('products.show');

// Корзина (отображается через Vue)

Route::get('/cart', function() {
    // Создаем файл cart.blade.php, если его нет
    $cartViewPath = resource_path('views/cart.blade.php');
    if (!file_exists($cartViewPath)) {
        // Создаем директорию, если её нет
        if (!is_dir(resource_path('views'))) {
            mkdir(resource_path('views'), 0755, true);
        }
        
        // Создаем простой шаблон для корзины
        $cartViewContent = <<<EOD
@extends('layouts.main')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div id="cart-page" data-vue-component="cart-page"></div>
</div>
@endsection
EOD;
        file_put_contents($cartViewPath, $cartViewContent);
    }
    
    // Используем Blade-шаблон для корзины
    return view('cart');
})->name('cart.index');

// Все операции с корзиной перенесены в API-маршруты (routes/api.php)

// Оформление заказа (рендерится Vue-компонентом в будущем)
Route::get('/checkout', function() {
    return view('checkout');
})->name('cart.checkout.form');

// Обработка заказа перенесена в API (/api/cart/checkout)

// Избранное и заказы (только для авторизованных)
Route::middleware('auth')->group(function () {
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/add/{product}', [FavoriteController::class, 'add'])->name('favorites.add');
    Route::post('/favorites/remove/{product}', [FavoriteController::class, 'remove'])->name('favorites.remove');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

// Страница подтверждения заказа (доступна всем, но с проверкой принадлежности заказа)
Route::get('/orders/{order}/confirmation', [OrderController::class, 'confirmation'])->name('orders.confirmation');

// FAQ
Route::get('/faq', [FaqController::class, 'index'])->name('faq');

// Sitemap
Route::get('/sitemap.xml', function () {
    $path = public_path('sitemap.xml');
    
    if (!file_exists($path)) {
        // Generate sitemap if it doesn't exist
        \Artisan::call('sitemap:generate');
    }
    
    return response()->file($path, [
        'Content-Type' => 'application/xml'
    ]);
})->name('sitemap');

// Contact form
use App\Http\Controllers\ContactController;
Route::post('/contact', [ContactController::class, 'sendMessage'])
    ->name('contact.send')
    ->middleware('throttle:5,10'); // Максимум 5 запросов за 10 минут

// Перенесенные API-маршруты для корзины
Route::prefix('api/cart')->group(function () {
    // Получить количество товаров в корзине
    Route::get('/count', [ApiCartController::class, 'getCount']);

    // Получить содержимое корзины
    Route::get('/', [ApiCartController::class, 'index']);
    
    // Добавить товар в корзину
    Route::post('/add/{productId}', [ApiCartController::class, 'add']);

    // Обновить количество товара
    Route::patch('/items/{itemId}', [ApiCartController::class, 'update']);

    // Удалить товар из корзины
    Route::delete('/items/{itemId}', [ApiCartController::class, 'destroy']);
});

// Перенесенные API-маршруты для оформления заказа
Route::prefix('api/checkout')->group(function () {
    // Получить данные для чекаута
    Route::get('/data', [CheckoutController::class, 'getData']);
    
    // Обработать оформление заказа
    Route::post('/process', [CheckoutController::class, 'process']);
});

// API-маршруты для купонов (используют web middleware для сессий)
use App\Http\Controllers\Api\CouponController;
Route::prefix('api/cart')->group(function () {
    // Apply coupon to cart (защита от DDoS: 5 попыток в минуту)
    Route::post('/apply-coupon', [CouponController::class, 'applyCoupon'])
         ->middleware('throttle:5,1');
    
    // Remove specific coupon from cart
    Route::post('/remove-coupon', [CouponController::class, 'removeCoupon'])
         ->middleware('throttle:10,1');
    
    // Remove all coupons from cart
    Route::post('/remove-all-coupons', [CouponController::class, 'removeAllCoupons'])
         ->middleware('throttle:10,1');
    
    // Get current cart calculation with applied coupons
    Route::get('/calculation', [CouponController::class, 'getCartCalculation'])
         ->middleware('throttle:30,1');
});

// Admin routes for Zelle payment details
use App\Http\Controllers\Admin\ZellePaymentController;
Route::prefix('admin/zelle')->middleware(['auth', 'admin'])->group(function () {
    Route::post('/send-payment-details/{zelleAddress}', [ZellePaymentController::class, 'sendPaymentDetailsRoute'])
         ->name('admin.zelle.send-payment-details');
});

// Payment routes
use App\Http\Controllers\PaymentController;

Route::prefix('payment')->name('payment.')->group(function () {
    // Create crypto payment
    Route::post('/crypto', [PaymentController::class, 'createCryptoPayment'])->name('crypto.create');
    
    // IPN callback (exclude from CSRF protection)
    Route::post('/ipn', [PaymentController::class, 'handleIPN'])->name('ipn')->withoutMiddleware('web');
    
    // Success/Cancel/Partial/Failed pages
    Route::get('/success', [PaymentController::class, 'paymentSuccess'])->name('success');
    Route::get('/cancel', [PaymentController::class, 'paymentCancel'])->name('cancel');
    Route::get('/partial', [PaymentController::class, 'paymentPartial'])->name('partial');
    Route::get('/failed', [PaymentController::class, 'paymentFailed'])->name('failed');
    
    // Get available currencies
    Route::get('/currencies', [PaymentController::class, 'getAvailableCurrencies'])->name('currencies');
});
