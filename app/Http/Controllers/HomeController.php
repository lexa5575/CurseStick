<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Отображение главной страницы с использованием Blade
     */
    public function index(): View
    {
        // Строго получаем ТОЛЬКО рекомендуемые товары
        $featuredProducts = Product::with('category')
            ->where('is_active', true)
            ->where('is_featured', true)
            ->latest()
            ->take(8)
            ->get();
            
        // Мы ПОЛНОСТЬЮ отключаем логику автоматического добавления нерекомендуемых товаров
        // Если на главной странице нет рекомендуемых товаров, раздел будет пустым
        // Это сделано по явному запросу пользователя
            
        // Получаем все категории с подсчетом товаров
        $categories = Category::withCount('products')
            ->get();
            
        // Получаем активные баннеры для слайдера
        $banners = Banner::where('is_active', true)
            ->orderBy('order')
            ->get();
            
        // Получаем активные платежные методы
        $paymentMethods = PaymentMethod::where('is_active', true)
            ->orderBy('display_order')
            ->get();
            
        // Возвращаем Blade-шаблон с данными
        return view('home', [
            'featuredProducts' => $featuredProducts,
            'categories' => $categories,
            'banners' => $banners,
            'paymentMethods' => $paymentMethods,
        ]);
    }
}
