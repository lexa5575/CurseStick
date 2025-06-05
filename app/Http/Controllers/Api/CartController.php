<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Получить количество товаров в корзине
     * Маршрут: GET /api/cart/count
     */
    public function getCount()
    {
        $cart = $this->getCart();
        $count = 0;

        if ($cart) {
            $count = $cart->items()->sum('quantity');
        }

        return response()->json([
            'count' => (int) $count
        ]);
    }

    /**
     * Получить содержимое корзины
     * Маршрут: GET /api/cart
     */
    public function index()
    {
        $cart = $this->getCart();
        $items = [];
        $total = 0;
        
        // Подготавливаем cookie для ответа
        $cookie = null;
        if ($cart) {
            $cookie = cookie()->forever('cruisestick_cart_id', $cart->id);
        }

        if ($cart) {
            $cartItems = $cart->items()->with('itemable')->get();

            foreach ($cartItems as $item) {
                if ($item->itemable instanceof Product) {
                    $product = $item->itemable;
                    $itemTotal = $item->total;
                    $total += $itemTotal;

                    $productData = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'image' => $product->image ?? '/images/placeholder.jpg',
                        'image_url' => $product->image_url, // Добавляем атрибут image_url
                        'price' => (float) ($product->discount ? ($product->price - $product->discount) : $product->price),
                        'category' => [
                            'id' => $product->category ? $product->category->id : null,
                            'name' => $product->category ? $product->category->name : 'Без категории'
                        ]
                    ];

                    $items[] = [
                        'id' => $item->id,
                        'product' => $productData,
                        'quantity' => $item->quantity,
                        'price' => (float) $item->price,
                        'total' => (float) $itemTotal
                    ];
                }
            }
        }

        return response()->json([
            'items' => $items,
            'total' => (float) $total,
            'count' => count($items)
        ]);
    }

    /**
     * Добавить товар в корзину
     * Маршрут: POST /api/cart/add/{product}
     */
    public function add(Product $product, Request $request)
    {
        try {
            // Валидация количества товара
            $quantity = (int) $request->input('quantity', 1);
            if ($quantity < 1) {
                $quantity = 1;
            }
            
            // Получаем или создаем корзину
            $cart = $this->getOrCreateCart();

            // Проверяем, есть ли уже этот товар в корзине
            $existingItem = $cart->items()
                ->where('itemable_id', $product->id)
                ->where('itemable_type', get_class($product))
                ->first();

            if ($existingItem) {
                // Если товар уже есть, увеличиваем количество
                $existingItem->quantity += $quantity;
                $existingItem->save();
                $itemId = $existingItem->id;
            } else {
                // Если товара еще нет, добавляем новый
                $cartItem = $cart->items()->create([
                    'itemable_id' => $product->id,
                    'itemable_type' => get_class($product),
                    'quantity' => $quantity,
                    'price' => $product->discount ? ($product->price - $product->discount) : $product->price,
                ]);
                $itemId = $cartItem->id;
            }
            
            // Убедимся, что изменения сохранены в сессии
            session()->save();

            // Возвращаем количество товаров в корзине
            $count = $cart->items()->sum('quantity');
            
            // Получаем обновленный элемент корзины
            $updatedItem = $cart->items()
                ->with('itemable')
                ->where('id', $itemId)
                ->first();
            
            // Подготавливаем данные товара для ответа
            $itemData = null;
            if ($updatedItem && $updatedItem->itemable instanceof Product) {
                $product = $updatedItem->itemable;
                $itemData = [
                    'id' => $updatedItem->id,
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'image' => $product->image ?? '/images/placeholder.jpg',
                        'category' => [
                            'id' => $product->category ? $product->category->id : null,
                            'name' => $product->category ? $product->category->name : 'Без категории'
                        ]
                    ],
                    'quantity' => $updatedItem->quantity,
                    'price' => (float) $updatedItem->price,
                    'total' => (float) ($updatedItem->quantity * $updatedItem->price)
                ];
            }
            
            $successMessage = $existingItem ? 'Количество товара в корзине увеличено' : 'Товар добавлен в корзину';

            // Подготавливаем данные для ответа
            $response = [
                'success' => true,
                'item_id' => $itemId,
                'count' => (int) $count,
                'item' => $itemData,
                'message' => $successMessage
            ];
            
            // Явно сохраняем сессию и фиксируем её
            session()->save();
            
            // Устанавливаем cookie с ID корзины
            $cookie = cookie()->forever('cruisestick_cart_id', $cart->id);
            

            
            // Проверяем, является ли запрос AJAX-запросом
            if ($request->ajax() || $request->wantsJson()) {
                // Если это AJAX-запрос, возвращаем JSON с cookie
                return response()->json($response)->withCookie($cookie);
            } else {
                // Если это обычный запрос, перенаправляем назад с сообщением
                return redirect()->back()->with('success', $successMessage);
            }
        } catch (\Exception $e) {
            // Обработка ошибок
            $errorMessage = 'Произошла ошибка при добавлении товара в корзину: ' . $e->getMessage();
            
            
            // Проверяем, является ли запрос AJAX-запросом
            if ($request->ajax() || $request->wantsJson()) {
                // Если это AJAX-запрос, возвращаем JSON
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            } else {
                // Если это обычный запрос, перенаправляем назад с сообщением
                return redirect()->back()->with('error', $errorMessage);
            }
        }
    }

    /**
     * Обновить количество товара в корзине
     * Маршрут: PATCH /api/cart/items/{id}
     */
    public function update(Request $request, $itemId)
    {
        try {
            $cart = $this->getCart();

            if (!$cart) {
                return response()->json(['error' => 'Корзина не найдена'], 404);
            }

            $quantity = $request->input('quantity');

            if (!is_numeric($quantity) || $quantity < 1) {
                return response()->json(['error' => 'Неверное количество'], 400);
            }

            $item = $cart->items()->find($itemId);

            if (!$item) {
                return response()->json(['error' => 'Товар не найден в корзине'], 404);
            }

            $item->quantity = $quantity;
            $item->save();
            
            // Сохраняем изменения в сессии
            session()->save();
            
            // Подсчитываем общее количество товаров в корзине
            $count = $cart->items()->sum('quantity');
            $itemTotal = $item->price * $quantity;

            return response()->json([
                'success' => true,
                'item_id' => $itemId,
                'quantity' => $quantity,
                'item_total' => (float) $itemTotal,
                'count' => (int) $count,
                'message' => 'Количество товара успешно обновлено'
            ]);
        } catch (\Exception $e) {
            // Обработка ошибок
            $errorMessage = 'Произошла ошибка при обновлении количества товара: ' . $e->getMessage();
            
            // Записываем ошибку в лог
            \Log::error('Error updating cart item: ' . $e->getMessage(), [
                'item_id' => $itemId,
                'quantity' => $quantity ?? 1,
                'exception' => $e
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 500);
        }
    }

    /**
     * Удалить товар из корзины
     * Маршрут: DELETE /api/cart/items/{id}
     */
    public function destroy($itemId)
    {
        try {
            $cart = $this->getCart();

            if (!$cart) {
                return response()->json(['error' => 'Корзина не найдена'], 404);
            }

            $item = $cart->items()->find($itemId);

            if (!$item) {
                return response()->json(['error' => 'Товар не найден в корзине'], 404);
            }

            $item->delete();
            
            // Сохраняем изменения в сессии
            session()->save();
            
            // Подсчитываем общее количество товаров в корзине после удаления
            $count = $cart->items()->sum('quantity');
            $itemsCount = $cart->items()->count();

            return response()->json([
                'success' => true,
                'item_id' => $itemId,
                'count' => (int) $count,
                'items_count' => (int) $itemsCount,
                'message' => 'Товар успешно удален из корзины'
            ]);
        } catch (\Exception $e) {
            // Обработка ошибок
            $errorMessage = 'Произошла ошибка при удалении товара из корзины: ' . $e->getMessage();
            
            // Записываем ошибку в лог
            \Log::error('Error removing item from cart: ' . $e->getMessage(), [
                'item_id' => $itemId,
                'exception' => $e
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 500);
        }
    }
    
    /**
     * Получить текущую корзину пользователя
     */
    protected function getCart()
    {
        // Проверяем cookie с ID корзины
        $cookieCartId = request()->cookie('cruisestick_cart_id');
        
        $userId = auth()->id();
        $sessionId = session()->getId();
        
        // Пробуем получить ID корзины из сессии или cookie
        $cartId = session('cart_id') ?: $cookieCartId;
        
        $cart = null;
        
        // Сначала пробуем найти корзину по ID из сессии
        if ($cartId) {
            $cart = Cart::find($cartId);
            
            // Если нашли, обновляем session_id
            if ($cart) {
                $cart->session_id = $sessionId;
                $cart->save();
                return $cart;
            }
        }
        
        // Затем пробуем найти по пользователю
        if ($userId) {
            $cart = Cart::where('user_id', $userId)->first();
        }
        
        // Если не нашли, пробуем найти по session_id
        if (!$cart && $sessionId) {
            $cart = Cart::where('session_id', $sessionId)->first();
        }
        
        // Сохраняем ID корзины в сессию для будущих запросов
        if ($cart) {
            session(['cart_id' => $cart->id]);
            session()->save();
        }
        
        return $cart;
    }
    
    /**
     * Получить существующую или создать новую корзину
     */
    protected function getOrCreateCart()
    {
        $userId = auth()->id();
        $sessionId = session()->getId();
        
        // Отладочная информация перед созданием корзины
        
        // Убедимся, что сессия сохранена
        session()->save();
        
        // Пробуем найти существующую корзину
        $cart = $this->getCart();
        
        // Если корзина найдена, просто возвращаем её
        if ($cart) {
            return $cart;
        }
        
        // Иначе создаем новую корзину
        if ($userId) {
            // Для авторизованных пользователей
            $cart = Cart::create([
                'user_id' => $userId,
                'session_id' => $sessionId
            ]);
        } else {
            // Для гостей
            $cart = Cart::create([
                'user_id' => null,
                'session_id' => $sessionId
            ]);
        }
        
        // Сохраняем ID корзины в сессию
        session(['cart_id' => $cart->id]);
        
        // Обновляем cookie
        cookie()->queue(
            'cart_session', 
            $cart->id, 
            60 * 24 * 30  // 30 дней
        );
        
        session()->save();
        
        
        return $cart;
    }
}
