<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Новый заказ на сайте</title>
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
            background-color: #2d3748;
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
        .alert {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin: 15px 0;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
        }
        .customer-info {
            background-color: #e2f0d9;
            padding: 10px;
            margin: 15px 0;
            border: 1px solid #c6e0b4;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Новый заказ на сайте</h1>
        </div>
        
        <div class="alert">
            <p><strong>Требуется внимание!</strong> На сайте был оформлен новый заказ #{{ $order->id }}.</p>
        </div>
        
        <div class="order-details">
            <h2>Информация о заказе #{{ $order->id }}</h2>
            <p><strong>Дата заказа:</strong> {{ $order->created_at->format('d.m.Y H:i') }} UTC</p>
            <p><strong>Способ оплаты:</strong> {{ ucfirst($order->payment_method) }}</p>
            <p><strong>Статус оплаты:</strong> {{ $order->payment_status }}</p>
            <p><strong>Общая стоимость:</strong> ${{ number_format($order->total, 2) }}</p>
            
            <div class="customer-info">
                <h3>Информация о клиенте:</h3>
                <p><strong>Имя:</strong> {{ $order->name }}</p>
                <p><strong>Email:</strong> {{ $order->email }}</p>
                @if($order->phone)
                <p><strong>Телефон:</strong> {{ $order->phone }}</p>
                @endif
                @if($order->company)
                <p><strong>Компания:</strong> {{ $order->company }}</p>
                @endif
            </div>
            
            <h3>Адрес доставки:</h3>
            <p>
                {{ $order->street }} @if($order->house), {{ $order->house }}@endif<br>
                {{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}<br>
                {{ $order->country }}
            </p>
            
            @if($order->comment)
            <h3>Комментарий к заказу:</h3>
            <p>{{ $order->comment }}</p>
            @endif
            
            <h3>Заказанные товары:</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Товар</th>
                        <th>Количество</th>
                        <th>Цена</th>
                        <th>Итого</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->id }}</td>
                        <td>
                            <div class="product-info">
                                @php
                                    // Получаем полный URL изображения для email
                                    $imageUrl = $item->product->image_url;
                                    // Если это относительный путь, добавляем домен
                                    if (!str_starts_with($imageUrl, 'http')) {
                                        $imageUrl = config('app.url') . '/' . ltrim($imageUrl, '/');
                                    }
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
                <p>Общая стоимость заказа: ${{ number_format($order->total, 2) }}</p>
            </div>
        </div>
        
        <p>Пожалуйста, обработайте заказ как можно скорее.</p>
        
        <div class="footer">
            <p>Это автоматическое уведомление. Пожалуйста, не отвечайте на это письмо.</p>
            <p>&copy; {{ date('Y') }} CruseStick. Все права защищены.</p>
        </div>
    </div>
</body>
</html>
