<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FavoriteController extends Controller
{
    /**
     * Показать список избранных товаров
     */
    public function index()
    {
        // Проверяем, что пользователь авторизован
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Для просмотра избранных товаров необходимо авторизоваться.');
        }
        
        // Получаем избранные товары пользователя
        $favorites = Favorite::where('user_id', auth()->id())
            ->with(['product', 'product.category'])
            ->get()
            ->map(function ($favorite) {
                return [
                    'id' => $favorite->id,
                    'product' => $favorite->product,
                ];
            });
            
        return Inertia::render('Favorites/Index', [
            'favorites' => $favorites,
        ]);
    }
    
    /**
     * Добавить товар в избранное
     */
    public function add(Product $product)
    {
        // Проверяем, что пользователь авторизован
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Для добавления в избранное необходимо авторизоваться.');
        }
        
        // Проверяем, есть ли уже этот товар в избранном
        $exists = Favorite::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->exists();
            
        if (!$exists) {
            Favorite::create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
            ]);
        }
        
        return back()->with('success', 'Товар добавлен в избранное!');
    }
    
    /**
     * Удалить товар из избранного
     */
    public function remove(Favorite $favorite)
    {
        // Проверяем, что этот товар принадлежит текущему пользователю
        if (auth()->id() !== $favorite->user_id) {
            return back()->with('error', 'Вы не можете удалить чужой товар из избранного.');
        }
        
        $favorite->delete();
        
        return back()->with('success', 'Товар удален из избранного.');
    }
}
