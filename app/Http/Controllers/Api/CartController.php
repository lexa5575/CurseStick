<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;

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

        // Проверяем что корзина существует перед получением товаров
        if ($cart) {
            // Оптимизированная загрузка с eager loading для избежания N+1
            $cartItems = $cart->items()->with(['itemable.category'])->get();

            foreach ($cartItems as $item) {
                if ($item->itemable instanceof Product) {
                    $product = $item->itemable;
                    $itemTotal = $item->total;
                    $total += $itemTotal;

                    $productData = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'image' => $product->image ?? '/images/placeholder.jpg',
                        'image_url' => $product->image_url,
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

        // Get coupon calculation if cart exists
        $couponData = [];
        $finalTotal = $total;
        $totalDiscount = 0;
        $itemDiscounts = [];
        
        if ($cart) {
            $couponService = app(\App\Services\CouponService::class);
            $calculation = $couponService->getCurrentCartCalculation($cart);
            $appliedCoupons = $couponService->getAppliedCoupons();
            
            $finalTotal = $calculation['final_total'];
            $totalDiscount = $calculation['total_discount'];
            $itemDiscounts = $calculation['item_discounts'];
            
            // Create item discount lookup for easy access
            $itemDiscountLookup = [];
            foreach ($itemDiscounts as $discount) {
                $itemDiscountLookup[$discount['item_id']] = $discount;
            }
            
            // Update items with individual discount information
            foreach ($items as &$item) {
                if (isset($itemDiscountLookup[$item['id']])) {
                    $discountInfo = $itemDiscountLookup[$item['id']];
                    $item['discount_amount'] = floor($discountInfo['discount_amount']);
                    $item['final_total'] = (float) $discountInfo['final_total'];
                    $item['has_discount'] = $discountInfo['discount_amount'] > 0;
                } else {
                    $item['discount_amount'] = 0.0;
                    $item['final_total'] = (float) $item['total'];
                    $item['has_discount'] = false;
                }
            }
            
            if (!empty($appliedCoupons)) {
                $couponData = [
                    'applied_coupons' => array_map(function($coupon) {
                        return [
                            'id' => $coupon->id,
                            'code' => $coupon->code,
                            'name' => $coupon->name,
                            'discount_type' => $coupon->discount_type,
                            'discount_value' => $coupon->discount_value,
                        ];
                    }, $appliedCoupons),
                    'total_discount' => floor($totalDiscount),
                    'original_total' => $total,
                    'final_total' => $finalTotal,
                    'item_discounts' => $itemDiscounts
                ];
            }
        }

        return response()->json([
            'items' => $items,
            'total' => (float) $total,
            'final_total' => (float) $finalTotal,
            'total_discount' => floor($totalDiscount),
            'count' => count($items),
            'coupons' => $couponData
        ]);
    }

    /**
     * Добавить товар в корзину
     * Маршрут: POST /api/cart/add/{productId}
     */
    public function add($productId, AddToCartRequest $request)
    {
        try {
            // Проверяем что товар существует и активен
            $product = Product::where('id', $productId)
                ->where('is_active', true)
                ->firstOrFail();

            // Получаем валидированное количество из Form Request
            $quantity = $request->validated()['quantity'];

            // Получаем или создаем корзину
            $cart = $this->getOrCreateCart();

            // Проверяем, есть ли уже этот товар в корзине
            $existingItem = $cart->items()
                ->where('itemable_id', $product->id)
                ->where('itemable_type', get_class($product))
                ->first();

            if ($existingItem) {
                // Проверяем не превысит ли новое количество лимит (100 из Form Request)
                $newQuantity = $existingItem->quantity + $quantity;
                if ($newQuantity > 100) { // Лимит из AddToCartRequest
                    return $this->handleError(
                        $request,
                        'Максимальное количество товара: 100',
                        400
                    );
                }

                $existingItem->quantity = $newQuantity;
                $existingItem->save();
                $itemId = $existingItem->id;
            } else {
                // Безопасное создание нового товара в корзине
                $cartItem = new CartItem();
                $cartItem->cart_id = $cart->id;
                $cartItem->itemable_id = $product->id;
                $cartItem->itemable_type = get_class($product);
                $cartItem->quantity = $quantity;
                $cartItem->price = $product->discount ?
                    ($product->price - $product->discount) : $product->price;
                $cartItem->save();

                $itemId = $cartItem->id;
            }

            // Возвращаем количество товаров в корзине
            $count = $cart->items()->sum('quantity');

            // Получаем обновленный элемент корзины с оптимизированной загрузкой
            $updatedItem = $cart->items()
                ->with(['itemable.category'])
                ->where('id', $itemId)
                ->first();

            // Подготавливаем данные товара для ответа
            $itemData = $this->formatCartItemData($updatedItem);

            $successMessage = $existingItem ?
                'Количество товара в корзине увеличено' : 'Товар добавлен в корзину';

            // Подготавливаем данные для ответа
            $response = [
                'success' => true,
                'item_id' => $itemId,
                'count' => (int) $count,
                'item' => $itemData,
                'message' => $successMessage
            ];

            return $this->handleSuccessResponse($request, $response, $successMessage);
            
        } catch (\Exception $e) {
            $errorMessage = 'Произошла ошибка при добавлении товара в корзину: ' . $e->getMessage();

            // Логируем ошибку
            \Log::error('Error adding item to cart: ' . $e->getMessage(), [
                'product_id' => $productId,
                'quantity' => $request->input('quantity', 1),
                'exception' => $e
            ]);

            return $this->handleError($request, $errorMessage);
        }
    }

    /**
     * Обновить количество товара в корзине
     * Маршрут: PATCH /api/cart/items/{id}
     */
    public function update(UpdateCartItemRequest $request, $itemId)
    {
        try {
            $cart = $this->getCart();

            if (!$cart) {
                return response()->json(['error' => 'Корзина не найдена'], 404);
            }

            // Получаем валидированное количество из Form Request
            $quantity = $request->validated()['quantity'];

            $item = $cart->items()->find($itemId);

            if (!$item) {
                return response()->json(['error' => 'Товар не найден в корзине'], 404);
            }

            $item->quantity = $quantity;
            $item->save();

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
            $errorMessage = 'Произошла ошибка при обновлении количества товара: ' . $e->getMessage();

            \Log::error('Error updating cart item: ' . $e->getMessage(), [
                'item_id' => $itemId,
                'quantity' => $request->input('quantity'),
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
            $errorMessage = 'Произошла ошибка при удалении товара из корзины: ' . $e->getMessage();

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
        $userId = auth()->id();
        $sessionId = session()->getId();

        // Для авторизованных пользователей ищем по user_id
        if ($userId) {
            $cart = Cart::where('user_id', $userId)
                ->where('expires_at', '>', now())  // Проверяем срок действия
                ->first();
            
            // If cart found but has different session_id, update it to current session
            if ($cart && $cart->session_id !== $sessionId) {
                $cart->session_id = $sessionId;
                $cart->save();
                
                // Update session with cart_id for compatibility
                session(['cart_id' => $cart->id]);
            }
            
            return $cart;
        }

        // Для гостей ищем по session_id
        return Cart::where('session_id', $sessionId)
            ->where('expires_at', '>', now())  // Проверяем срок действия
            ->first();
    }

    /**
     * Получить существующую или создать новую корзину
     */
    protected function getOrCreateCart()
    {
        $cart = $this->getCart();

        if ($cart) {
            // Обновляем срок действия корзины при активности
            $cart->expires_at = now()->addDays(30);
            $cart->save();
            return $cart;
        }

        // Безопасное создание корзины
        $cart = new Cart();
        $cart->user_id = auth()->id();
        $cart->session_id = session()->getId();
        $cart->expires_at = now()->addDays(30);
        $cart->save();

        return $cart;
    }

    /**
     * Обработка ошибок с учетом типа запроса
     */
    private function handleError($request, $message, $code = 500)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $message], $code);
        }

        return redirect()->back()->with('error', $message);
    }

    /**
     * Обработка успешного ответа с учетом типа запроса
     */
    private function handleSuccessResponse($request, $jsonResponse, $message)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($jsonResponse);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Форматирование данных товара в корзине
     */
    private function formatCartItemData($cartItem)
    {
        if (!$cartItem || !($cartItem->itemable instanceof Product)) {
            return null;
        }

        $product = $cartItem->itemable;

        return [
            'id' => $cartItem->id,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->image ?? '/images/placeholder.jpg',
                'category' => [
                    'id' => $product->category ? $product->category->id : null,
                    'name' => $product->category ? $product->category->name : 'Без категории'
                ]
            ],
            'quantity' => $cartItem->quantity,
            'price' => (float) $cartItem->price,
            'total' => (float) ($cartItem->quantity * $cartItem->price)
        ];
    }
}