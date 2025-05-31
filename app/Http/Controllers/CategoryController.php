<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        // Загрузка всех категорий с их количеством товаров
        $categories = Category::withCount('products')
            ->get();
            
        return view('categories.index', [
            'categories' => $categories,
        ]);
    }

    public function show(Category $category)
    {
        // Загрузка конкретной категории с её товарами
        $products = Product::where('category_id', $category->id)
            ->where('is_active', true)
            ->get();
        
        // Дополнительные категории для навигации
        $otherCategories = Category::where('id', '!=', $category->id)
            ->withCount('products')
            ->take(4)
            ->get();
        
        // Получаем все категории для выпадающего меню в шапке
        $categories = Category::all();
        
        return view('categories.show', [
            'category' => $category,
            'products' => $products,
            'otherCategories' => $otherCategories,
            'categories' => $categories, // Добавлена переменная для выпадающего меню
        ]);
    }
}
