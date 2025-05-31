<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category; // Добавлено использование модели Category
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        // Загрузка продукта с его категорией и изображениями
        $product->load(['category']);
        
        // Похожие товары из той же категории
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->take(4)
            ->get();
        
        // Получаем все категории для выпадающего меню в шапке
        $categories = Category::all();
        
        return view('products.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'categories' => $categories, // Добавлена переменная для выпадающего меню
        ]);
    }
}
