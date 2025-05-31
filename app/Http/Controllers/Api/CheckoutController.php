<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\ProcessCheckoutRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Notifications\OrderConfirmation;
use App\Notifications\AdminOrderNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * Получить данные для формы оформления заказа
     * Маршрут: GET /api/checkout/data
     */
    public function getData()
    {
        // Сначала найдем корзину пользователя
        $cartId = session('cart_id');
        $cart = null;

        // Если пользователь авторизован, ищем корзину по user_id
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
        }
        
        // Если корзина не найдена, ищем по session_id
        if (!$cart && $cartId) {
            $cart = Cart::find($cartId);
        }
        
        // Если корзина не найдена, ищем по текущему session_id
        if (!$cart) {
            $cart = Cart::where('session_id', session()->getId())->first();
        }

        $cartItems = [];
        $total = 0;

        if ($cart) {
            $items = $cart->items()->with('itemable')->get();
            
            foreach ($items as $item) {
                if ($item->itemable instanceof Product) {
                    $cartItems[] = [
                        'id' => $item->id,
                        'product' => [
                            'id' => $item->itemable->id,
                            'name' => $item->itemable->name,
                            'image' => $item->itemable->image ?? '/images/placeholder.jpg',
                        ],
                        'quantity' => $item->quantity,
                        'price' => (float) $item->price,
                        'total' => (float) ($item->quantity * $item->price),
                    ];
                    $total += $item->quantity * $item->price;
                }
            }
        }

        // Данные пользователя, если он авторизован
        $userData = [];
        if (Auth::check()) {
            $user = Auth::user();
            $userData = [
                'name' => $user->name,
                'email' => $user->email,
                // Дополнительные данные из профиля, если есть
                'phone' => $user->phone ?? '',
                'address' => $user->address ?? '',
            ];
        }

        // Список доступных способов оплаты
        $paymentMethods = [
            ['id' => 'zelle', 'name' => 'Zelle (Instant bank transfers)'],
            ['id' => 'crypto', 'name' => 'Cryptocurrency (Bitcoin, Ethereum, etc.)'],
            ['id' => 'cash', 'name' => 'Cash on delivery'],
            ['id' => 'card', 'name' => 'Credit/Debit card'],
            ['id' => 'online', 'name' => 'Online payment'],
        ];

        return response()->json([
            'cartItems' => $cartItems,
            'total' => (float) $total,
            'isEmpty' => count($cartItems) === 0,
            'userData' => $userData,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Обработка оформления заказа
     * Маршрут: POST /api/checkout/process
     */
    public function process(ProcessCheckoutRequest $request)
    {   
        // Данные уже провалидированы благодаря ProcessCheckoutRequest

        // Получаем корзину
        $cartId = session('cart_id');
        $cart = null;

        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
        }
        
        if (!$cart && $cartId) {
            $cart = Cart::find($cartId);
        }
        
        if (!$cart) {
            $cart = Cart::where('session_id', session()->getId())->first();
        }

        // Если корзина пуста или не найдена
        if (!$cart || $cart->items()->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Ваша корзина пуста'
            ], 400);
        }

        try {
            // Начинаем транзакцию
            DB::beginTransaction();

            // Создаем заказ
            $order = new Order([
                'user_id' => Auth::check() ? Auth::id() : null,
                'status' => 'Новый',
                'total' => $cart->items()->sum(DB::raw('price * quantity')),
                // Сохраняем все компоненты адреса в соответствующие поля
                'company' => $request->company,
                'street' => $request->street, // Обновлено с address на street
                'house' => $request->house ?? '', // Обновлено с addressUnit на house
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code, // Обновлено с zipcode на postal_code
                'country' => $request->country,
                'phone' => $request->phone,
                'email' => $request->email,
                'name' => $request->name,
                'comment' => $request->comment,
                'payment_method' => $request->payment_method ?? 'none', // Обновлено с paymentMethod на payment_method
                'payment_status' => 'pending'
            ]);
            
            $order->save();

            // Создаем элементы заказа из корзины
            foreach ($cart->items as $item) {
                if ($item->itemable instanceof Product) {
                    $orderItem = new OrderItem([
                        'order_id' => $order->id,
                        'product_id' => $item->itemable->id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'discount' => 0, // По умолчанию без скидки
                    ]);
                    $orderItem->save();
                }
            }

            // Добавляем первую запись в историю статусов
            $statusHistory = new OrderStatusHistory([
                'order_id' => $order->id,
                'status' => 'Новый',
            ]);
            $statusHistory->save();

            // Очищаем корзину
            $cart->items()->delete();
            
            // Если все успешно, фиксируем транзакцию
            DB::commit();
            
            // Отправляем уведомление клиенту
            try {
                if ($order->email) {
                    Notification::route('mail', $order->email)
                        ->notify(new OrderConfirmation($order));
                }
                
                // Отправляем уведомление администратору
                $adminEmail = config('mail.admin_address', 'admin@crusestick.com');
                Notification::route('mail', $adminEmail)
                    ->notify(new AdminOrderNotification($order, $adminEmail));
                    
            } catch (\Exception $emailException) {
                // Логируем ошибку отправки, но не прерываем процесс заказа
                \Log::error('Ошибка отправки email-уведомления: ' . $emailException->getMessage());
            }

            // Гарантируем, что order_id передается правильно и в формате строки, чтобы избежать проблем с преобразованием типов
            return response()->json([
                'success' => true,
                'message' => 'Ваш заказ успешно оформлен!',
                'order_id' => (string)$order->id, // Явно преобразуем в строку
                'redirect_url' => '/orders/'.$order->id.'/confirmation' // Добавляем полный URL для перенаправления
            ]);
        } catch (\Exception $e) {
            // Если произошла ошибка, откатываем транзакцию
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при оформлении заказа. Пожалуйста, попробуйте позже.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
