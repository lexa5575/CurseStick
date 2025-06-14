@extends('emails.layouts.modern')

@section('title', 'Your Order Has Been Shipped - CruseStick')

@section('header', '📦 Your Order is On the Way!')

@section('content')
    <h2 style="color: #2d3748 !important; font-size: 22px !important; font-weight: 600 !important;">Order #{{ $order->id }} Shipped</h2>
    
    <p style="color: #4a5568 !important; font-size: 16px !important; font-weight: normal !important;">Dear {{ $order->name }},</p>
    
    <p style="color: #4a5568 !important; font-size: 16px !important; font-weight: normal !important;">Great news! Your order has been shipped and is on its way to you. Here are your tracking details:</p>
    
    <div class="info-box success">
        <h4 style="color: #2d3748 !important; font-size: 16px !important; font-weight: 600 !important;">📋 Shipping Information</h4>
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;">You can track the status of your delivery using the tracking number below:</p>
        
        <div style="text-align: center; margin: 20px 0;">
            <div style="background-color: white; padding: 20px; border-radius: 8px; border: 2px dashed #38a169; margin: 15px 0;">
                <p style="margin: 0 0 10px 0; font-size: 16px; color: #1a202c; font-weight: 600;">Tracking Number</p>
                <p style="margin: 0; font-family: monospace; font-size: 26px; font-weight: bold; color: #000000;">{{ $order->tracking_number }}</p>
            </div>
            <a href="https://tools.usps.com/go/TrackConfirmAction?tRef=fullpage&tLc=2&text28777=&tLabels={{ $order->tracking_number }}" class="button success" target="_blank">Track via USPS</a>
        </div>
    </div>
    
    @php
        $shippingDate = now();
        $estimatedDeliveryDate = $shippingDate->copy()->addDays(5);
        
        if ($estimatedDeliveryDate->dayOfWeek === 0) {
            $estimatedDeliveryDate->addDay();
        }
        
        $formattedShippingDate = $shippingDate->format('F j, Y');
        $formattedDeliveryDate = $estimatedDeliveryDate->format('F j, Y');
    @endphp
    
    <div class="info-box">
        <h4 style="color: #2d3748 !important; font-size: 16px !important; font-weight: 600 !important;">📅 Delivery Timeline</h4>
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Shipping Date:</strong> {{ $formattedShippingDate }}</p>
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Estimated Delivery:</strong> {{ $formattedDeliveryDate }}</p>
    </div>
    
    <h3 style="color: #2d3748 !important; font-size: 18px !important; font-weight: 600 !important;">Order Summary</h3>
    <table class="order-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    <div class="product-cell">
                        @php
                            $imageUrl = $item->product->image_url;
                            if (!str_starts_with($imageUrl, 'http')) {
                                $imageUrl = config('app.url') . '/' . ltrim($imageUrl, '/');
                            }
                        @endphp
                        <img src="{{ $imageUrl }}" alt="{{ $item->product->name }}" class="product-image">
                        <div class="product-details">
                            <h5>{{ $item->product->name }}</h5>
                            @if($item->product->discount > 0)
                                <span class="badge">SALE</span>
                            @endif
                        </div>
                    </div>
                </td>
                <td>{{ $item->quantity }}</td>
                <td>
                    @if($item->product->discount > 0)
                        <span style="text-decoration: line-through; color: #777; font-size: 90%;">${{ number_format($item->product->price, 2) }}</span><br>
                        ${{ number_format($item->product->price - $item->product->discount, 2) }}
                    @else
                        ${{ number_format($item->price, 2) }}
                    @endif
                </td>
                <td><strong>${{ number_format($item->price * $item->quantity, 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="total-section">
        <div class="total-row final">
            <span>Order Total:</span>
            <span>${{ number_format($order->total, 2) }}</span>
        </div>
    </div>
    
    <h3 style="color: #2d3748 !important; font-size: 18px !important; font-weight: 600 !important;">Shipping Address</h3>
    <div class="shipping-address">
        <h4 style="color: #2d3748 !important; font-size: 16px !important; font-weight: 600 !important;">Delivery Address</h4>
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;">
            {{ $order->name }}<br>
            @if($order->company){{ $order->company }}<br>@endif
            {{ $order->street }}@if($order->house), {{ $order->house }}@endif<br>
            {{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}<br>
            {{ $order->country }}
        </p>
    </div>
    
    <div style="text-align: center; margin: 30px 0; padding: 20px; background-color: #ffffff; border: 2px solid #c6f6d5; border-left: 4px solid #38a169; border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">
        <h4 style="color: #2d3748 !important; font-size: 16px !important; font-weight: 600 !important; margin: 0 0 10px 0;">🎉 Almost There!</h4>
        <p style="margin: 0; color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important; line-height: 1.5;">Your package should arrive within the estimated delivery timeframe. Keep an eye out for it!</p>
    </div>
    
    <p style="color: #4a5568 !important; font-size: 16px !important; font-weight: normal !important;">If you have any questions about your delivery, please don't hesitate to contact our support team.</p>
    
    <p style="margin-top: 30px; color: #4a5568 !important; font-size: 16px !important; font-weight: normal !important;">
        Best regards,<br>
        <strong style="color: #2d3748 !important; font-weight: 600 !important;">The CruseStick Team</strong>
    </p>
@endsection