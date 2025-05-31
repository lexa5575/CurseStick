<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index() {
        $products = \App\Models\Product::where('is_active', true)->paginate(12);
        $categories = \App\Models\Category::all(); // Добавляем категории для выпадающего меню
        return view('shop.index', compact('products', 'categories'));
    }

    public function show(\App\Models\Product $product) {
        $categories = \App\Models\Category::all(); // Добавляем категории для выпадающего меню
        return view('shop.show', compact('product', 'categories'));
    }
}
