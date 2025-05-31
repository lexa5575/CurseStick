<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Подтверждение заказа</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
        }
        .container {
            padding: 20px;
            background-color: #f9f9f9;
        }
        .header {
            background-color: #4a5568;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }
        .order-details {
            margin: 20px 0;
            border: 1px solid #ddd;
            padding: 15px;
            background-color: white;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ddd;
            padding: 8px 12px;
            text-align: left;
            vertical-align: middle;
        }
        .items-table th {
            background-color: #f2f2f2;
        }
        .total {
            margin-top: 20px;
            text-align: right;
            font-weight: bold;
        }
        .product-image {
            width: 80px;
            height: auto;
            border-radius: 4px;
            border: 1px solid #eee;
        }
        .product-info {
            display: flex;
            align-items: center;
        }
        .product-details {
            margin-left: 10px;
        }
        .discount-badge {
            display: inline-block;
            background-color: #e53e3e;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
            margin-left: 5px;
        }
        .original-price {
            text-decoration: line-through;
            color: #777;
            font-size: 90%;
        }
        .thank-you-message {
            font-size: 18px;
            color: #4a5568;
            margin: 20px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Спасибо за ваш заказ!</h1>
        </div>
        
        <p>Уважаемый(ая) {{ $order->name }},</p>
        
        <p>Мы рады сообщить, что ваш заказ был успешно оформлен. Вот детали вашего заказа:</p>
        
        <div class="order-details">
            <h2>Заказ #{{ $order->id }}</h2>
            <p><strong>Дата заказа:</strong> {{ $order->created_at->format('d.m.Y H:i') }} UTC</p>
            <p><strong>Способ оплаты:</strong> {{ ucfirst($order->payment_method) }}</p>
            <p><strong>Статус оплаты:</strong> {{ $order->payment_status }}</p>
            
            @if($order->payment_method === 'zelle')
                @if(isset($zelleAddress))
                <div style="background-color: #ebf8ff; border: 1px solid #4299e1; border-radius: 4px; padding: 15px; margin: 15px 0;">
                    <h3 style="color: #2b6cb0; margin-top: 0;">Информация об оплате через Zelle</h3>
                    <p>Для оплаты заказа пожалуйста отправьте <strong>${{ number_format($order->total, 2) }}</strong> на следующий Zelle адрес:</p>
                    <p style="background-color: white; padding: 10px; border-radius: 4px; border: 1px dashed #4299e1; font-family: monospace; font-size: 16px;">
                        <strong>{{ $zelleAddress->address }}</strong>
                    </p>
                    <p><small>Зарегистрирован на email: {{ $zelleAddress->email }}</small></p>
                    
                    @if($zelleAddress->note)
                    <p><strong>Примечание:</strong> {{ $zelleAddress->note }}</p>
                    @endif
                    
                    <p><strong>Важно:</strong> В комментарии к платежу обязательно укажите номер вашего заказа: <strong>#{{ $order->id }}</strong></p>
                </div>
                @else
                <div style="background-color: #f7fafc; border: 1px solid #cbd5e0; border-radius: 4px; padding: 15px; margin: 15px 0;">
                    <h3 style="color: #4a5568; margin-top: 0;">Информация об оплате через Zelle</h3>
                    <p>Вы выбрали способ оплаты через Zelle. Наш менеджер свяжется с вами в ближайшее время для предоставления реквизитов оплаты.</p>
                    <p>Общая сумма к оплате: <strong>${{ number_format($order->total, 2) }}</strong></p>
                    <p><strong>Важно:</strong> При выполнении оплаты, пожалуйста, укажите номер вашего заказа: <strong>#{{ $order->id }}</strong> в комментарии к платежу.</p>
                </div>
                @endif
            @endif
            
            <h3>Адрес доставки:</h3>
            <p>
                {{ $order->name }}<br>
                @if($order->company){{ $order->company }}<br>@endif
                {{ $order->street }} @if($order->house), {{ $order->house }}@endif<br>
                {{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}<br>
                {{ $order->country }}
            </p>
            
            @if($order->phone)
            <p><strong>Телефон:</strong> {{ $order->phone }}</p>
            @endif
            
            <h3>Заказанные товары:</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Товар</th>
                        <th>Количество</th>
                        <th>Цена</th>
                        <th>Итого</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>
                            <div class="product-info">
                                @php
                                    // В продакшене используйте эту строку
                                    // $imageUrl = config('app.url') . '/' . $item->product->image_url;
                                    // Для разработки используем надежную заглушку
                                    $imageUrl = 'https://picsum.photos/80/80?random=' . $item->product->id;
                                @endphp
                                <img src="{{ $imageUrl }}" alt="{{ $item->product->name }}" class="product-image">
                                <div class="product-details">
                                    {{ $item->product->name }}
                                    @if($item->product->discount > 0)
                                        <span class="discount-badge">СКИДКА</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td>
                            @if($item->product->discount > 0)
                                <span class="original-price">${{ number_format($item->product->price, 2) }}</span><br>
                                ${{ number_format($item->product->price - $item->product->discount, 2) }}
                            @else
                                ${{ number_format($item->price, 2) }}
                            @endif
                        </td>
                        <td>${{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="total">
                <p>Общая стоимость: ${{ number_format($order->total, 2) }}</p>
            </div>
        </div>
        
        <p>Если у вас возникли вопросы по поводу заказа, пожалуйста, свяжитесь с нами.</p>
        
        <p>С уважением,<br>Команда CruseStick</p>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} CruseStick. Все права защищены.</p>
        </div>
    </div>
</body>
</html>
