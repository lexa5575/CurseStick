<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Отображение списка заказов пользователя
     */
    public function index()
    {
        // Проверяем, что пользователь авторизован
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Для просмотра заказов необходимо авторизоваться.');
        }
        
        // Получаем все заказы текущего пользователя
        $orders = Order::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                // Форматируем дату для удобного отображения
                return [
                    'id' => $order->id,
                    'date' => $order->created_at->format('Y-m-d'),
                    'status' => $order->status,
                    'total' => $order->total,
                ];
            });
            
// Используем Blade-шаблон для отображения списка заказов
        if (!file_exists(resource_path('views/orders/index.blade.php'))) {
            // Если шаблона нет, показываем сообщение
            return view('layouts.main', [
                'content' => '<div class="container mx-auto px-4 py-6">
                    <h1 class="text-2xl font-bold mb-4">Список заказов</h1>
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
                        <p>Шаблон для списка заказов еще не создан. Используйте страницу подтверждения заказа.</p>
                    </div>
                </div>'
            ]);
        }
        
        return view('orders.index', [
            'orders' => $orders
        ]);
    }

    /**
     * Отображение деталей конкретного заказа
     */
    public function show(Order $order)
    {
        // Проверяем, что заказ принадлежит текущему пользователю
        if (auth()->id() !== $order->user_id) {
            return redirect()->route('orders.index')
                ->with('error', 'Вы не можете просматривать чужие заказы.');
        }
        
        // Загружаем связанные данные
        $order->load(['items.product', 'statusHistories']);
        
        // Форматируем данные для отображения
        $orderData = [
            'id' => $order->id,
            'date' => $order->created_at->format('Y-m-d'),
            'status' => $order->status,
            'total' => $order->total,
            'address' => "{г. $order->city, ул. $order->street, д. $order->house}" . 
                         ($order->postal_code ? ", $order->postal_code" : ''),
            'phone' => $order->phone,
            'email' => $order->email,
            'payment_method' => $order->payment_method,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->product->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'total' => $item->price * $item->quantity,
                ];
            }),
            'shipping' => 0, // Можно добавить стоимость доставки, если она есть
            'payment' => [
                'method' => $order->payment_method,
                'status' => $order->status === 'Оплачен' ? 'Оплачено' : 'Ожидается',
            ],
            'statusHistory' => $order->statusHistories->map(function ($history) {
                return [
                    'status' => $history->status,
                    'date' => $history->created_at->format('Y-m-d H:i'),
                ];
            }),
        ];
        
// Используем Blade-шаблон для отображения деталей заказа
        if (!file_exists(resource_path('views/orders/show.blade.php'))) {
            // Если шаблона нет, показываем сообщение
            return view('layouts.main', [
                'content' => '<div class="container mx-auto px-4 py-6">
                    <h1 class="text-2xl font-bold mb-4">Детали заказа #' . $order->id . '</h1>
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
                        <p>Шаблон для деталей заказа еще не создан. Используйте страницу подтверждения заказа.</p>
                    </div>
                </div>'
            ]);
        }
        
        return view('orders.show', [
            'order' => $order,
            'orderData' => $orderData
        ]);
    }

    /**
     * Отображение страницы подтверждения заказа
     */
    public function confirmation(Order $order)
    {
        // Проверяем, что заказ принадлежит текущему пользователю, если он авторизован
        if ($order->user_id && $order->user_id !== auth()->id()) {
            abort(403, 'Вы не можете просматривать чужие заказы.');
        }
        
        // Загружаем связанные данные
        $order->load(['items.product']);
        
        return view('orders.confirmation', [
            'order' => $order
        ]);
    }
}
