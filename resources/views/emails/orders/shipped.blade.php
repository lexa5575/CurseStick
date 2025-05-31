<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your Order Has Been Shipped</title>
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
            background-color: #10b981; /* Changed to green color */
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
        .tracking-info {
            background-color: #ebf8ff;
            border: 1px solid #4299e1;
            border-radius: 4px;
            padding: 15px;
            margin: 15px 0;
        }
        .tracking-number {
            margin: 15px 0;
            text-align: center;
        }
        .track-button {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 16px;
            text-align: center;
            margin: 10px 0;
            transition: background-color 0.3s;
        }
        .track-button:hover {
            background-color: #2563eb;
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
            <h1>Your Order Has Been Shipped! ✓</h1>
        </div>
        
        <p>Dear {{ $order->name }},</p>
        
        <p>We're pleased to inform you that your order #{{ $order->id }} has been shipped and will be delivered soon.</p>
        
        <div class="order-details">
            <h2>Order #{{ $order->id }}</h2>
            
            <div class="tracking-info">
                <h3 style="color: #2b6cb0; margin-top: 0;">Shipping Information</h3>
                <p>You can track the status of your delivery using the tracking number below:</p>
                
                <div class="tracking-number">
                    <p>Tracking Number: <strong>{{ $order->tracking_number }}</strong></p>
                    <a href="https://tools.usps.com/go/TrackConfirmAction?tRef=fullpage&tLc=2&text28777=&tLabels={{ $order->tracking_number }}" class="track-button" target="_blank">Track via USPS</a>
                </div>
                
                <p>You will receive a notification when your package is delivered.</p>
            </div>
            
            @php
                // Current date as shipping date
                $shippingDate = now();
                
                // Calculate estimated delivery date (5 days from now)
                $estimatedDeliveryDate = $shippingDate->copy()->addDays(5);
                
                // If the estimated delivery date is Sunday (0), add one more day
                if ($estimatedDeliveryDate->dayOfWeek === 0) {
                    $estimatedDeliveryDate->addDay();
                }
                
                // Format dates in US format (month/day/year)
                $formattedShippingDate = $shippingDate->format('m/d/Y');
                $formattedDeliveryDate = $estimatedDeliveryDate->format('m/d/Y');
            @endphp
            
            <p><strong>Shipping Date:</strong> {{ $formattedShippingDate }}</p>
            <p><strong>Estimated Delivery:</strong> {{ $formattedDeliveryDate }}</p>
            
            <h3>Order Summary:</h3>
            <table class="items-table" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 8px 12px; text-align: left; background-color: #f2f2f2;">Product</th>
                        <th style="border: 1px solid #ddd; padding: 8px 12px; text-align: left; background-color: #f2f2f2;">Quantity</th>
                        <th style="border: 1px solid #ddd; padding: 8px 12px; text-align: left; background-color: #f2f2f2;">Price</th>
                        <th style="border: 1px solid #ddd; padding: 8px 12px; text-align: left; background-color: #f2f2f2;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px 12px; text-align: left; vertical-align: middle;">
                            <div style="display: flex; align-items: center;">
                                @php
                                    // Get full URL of the image for email
                                    $imageUrl = $item->product->image_url;
                                    // If it's a relative path, add the domain
                                    if (!str_starts_with($imageUrl, 'http')) {
                                        $imageUrl = config('app.url') . '/' . ltrim($imageUrl, '/');
                                    }
                                @endphp
                                <img src="{{ $imageUrl }}" alt="{{ $item->product->name }}" style="width: 80px; height: auto; border-radius: 4px; border: 1px solid #eee;">
                                <div style="margin-left: 10px;">
                                    {{ $item->product->name }}
                                    @if($item->product->discount > 0)
                                        <span style="display: inline-block; background-color: #e53e3e; color: white; padding: 2px 6px; border-radius: 4px; font-size: 12px; margin-left: 5px;">SALE</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td style="border: 1px solid #ddd; padding: 8px 12px; text-align: left; vertical-align: middle;">{{ $item->quantity }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px 12px; text-align: left; vertical-align: middle;">
                            @if($item->product->discount > 0)
                                <span style="text-decoration: line-through; color: #777; font-size: 90%;">{{ number_format($item->product->price, 2) }}</span><br>
                                ${{ number_format($item->product->price - $item->product->discount, 2) }}
                            @else
                                ${{ number_format($item->price, 2) }}
                            @endif
                        </td>
                        <td style="border: 1px solid #ddd; padding: 8px 12px; text-align: left; vertical-align: middle;">${{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div style="margin-top: 20px; text-align: right; font-weight: bold;">
                <p>Total: ${{ number_format($order->total, 2) }}</p>
            </div>
            
            <h3>Shipping Address:</h3>
            <p>
                {{ $order->name }}<br>
                @if($order->company){{ $order->company }}<br>@endif
                {{ $order->street }} @if($order->house), {{ $order->house }}@endif<br>
                {{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}<br>
                {{ $order->country }}
            </p>
        </div>
        
        <p class="thank-you-message">Thank you for choosing CruseStick!</p>
        
        <p>If you have any questions about your delivery, please contact us.</p>
        
        <p>Best Regards,<br>The CruseStick Team</p>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} CruseStick. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
