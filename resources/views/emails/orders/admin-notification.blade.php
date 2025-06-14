@extends('emails.layouts.modern')

@section('title', 'New Order Alert - CruseStick Admin')

@section('header', '🔔 New Order Received')

@section('content')
    <div class="info-box warning">
        <h4 style="color: #2d3748 !important; font-size: 16px !important; font-weight: 600 !important;">⚠️ Action Required</h4>
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;">A new order has been placed on the website and requires your attention.</p>
    </div>
    
    <h2 style="color: #2d3748 !important; font-size: 22px !important; font-weight: 600 !important;">Order #{{ $order->id }} Details</h2>
    
    <div class="info-box">
        <h4 style="color: #2d3748 !important; font-size: 16px !important; font-weight: 600 !important;">Order Summary</h4>
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Order Number:</strong> #{{ $order->id }}</p>
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Order Date:</strong> {{ $order->created_at->format('F j, Y \a\t g:i A') }} UTC</p>
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Payment Status:</strong> {{ ucfirst($order->payment_status) }}</p>
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Total Amount:</strong> ${{ number_format($order->total, 2) }}</p>
    </div>
    
    <div class="info-box success">
        <h4 style="color: #2d3748 !important; font-size: 16px !important; font-weight: 600 !important;">Customer Information</h4>
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Name:</strong> {{ $order->name }}</p>
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Email:</strong> {{ $order->email }}</p>
        @if($order->phone)
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Phone:</strong> {{ $order->phone }}</p>
        @endif
        @if($order->company)
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Company:</strong> {{ $order->company }}</p>
        @endif
    </div>
    
    <h3 style="color: #2d3748 !important; font-size: 18px !important; font-weight: 600 !important;">Shipping Address</h3>
    <div class="shipping-address">
        <h4 style="color: #2d3748 !important; font-size: 16px !important; font-weight: 600 !important;">Delivery Address</h4>
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;">
            {{ $order->street }}@if($order->house), {{ $order->house }}@endif<br>
            {{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}<br>
            {{ $order->country }}
        </p>
    </div>
    
    @if($order->comment)
    <div class="info-box">
        <h4 style="color: #2d3748 !important; font-size: 16px !important; font-weight: 600 !important;">Order Notes</h4>
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;">{{ $order->comment }}</p>
    </div>
    @endif
    
    <h3 style="color: #2d3748 !important; font-size: 18px !important; font-weight: 600 !important;">Ordered Products</h3>
    <table class="order-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td><strong>{{ $item->product->id }}</strong></td>
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
        @if(isset($calculation) && isset($appliedCoupons) && !empty($appliedCoupons))
            <div class="total-row">
                <span>Subtotal:</span>
                <span>${{ number_format($calculation['original_total'], 2) }}</span>
            </div>
            @foreach($appliedCoupons as $coupon)
                <div class="total-row" style="color: #22c55e;">
                    <span>Applied Coupon {{ $coupon->code }} ({{ $coupon->name }}):</span>
                    <span>-${{ number_format($calculation['total_discount'], 2) }}</span>
                </div>
                <div class="total-row" style="font-size: 14px; color: #666;">
                    <span>Coupon Type:</span>
                    <span>{{ $coupon->discount_type === 'percentage' ? $coupon->discount_value . '%' : '$' . $coupon->discount_value }}</span>
                </div>
            @endforeach
            <div class="total-row final">
                <span>Final Order Total:</span>
                <span>${{ number_format($order->total, 2) }}</span>
            </div>
        @elseif($order->comment && str_contains($order->comment, 'Applied coupon:'))
            {{-- Show coupon info from order comment if available --}}
            @php
                // Extract coupon information from order comment
                $commentLines = explode("\n", $order->comment);
                $couponLines = array_filter($commentLines, fn($line) => str_contains($line, 'Applied coupon:'));
            @endphp
            @if(!empty($couponLines))
                @foreach($couponLines as $couponLine)
                    @php
                        // Parse coupon line: "Applied coupon: CODE (-AMOUNT USD)"
                        preg_match('/Applied coupon: ([A-Z0-9]+) \(-\$?([0-9\.]+)/', $couponLine, $matches);
                        $couponCode = $matches[1] ?? '';
                        $discountAmount = $matches[2] ?? '0';
                    @endphp
                    @if($couponCode)
                        <div class="total-row">
                            <span>Subtotal:</span>
                            <span>${{ number_format($order->total + floatval($discountAmount), 2) }}</span>
                        </div>
                        <div class="total-row" style="color: #22c55e;">
                            <span>Applied Coupon {{ $couponCode }}:</span>
                            <span>-${{ number_format($discountAmount, 2) }}</span>
                        </div>
                    @endif
                @endforeach
            @endif
            <div class="total-row final">
                <span>Final Order Total:</span>
                <span>${{ number_format($order->total, 2) }}</span>
            </div>
        @else
            <div class="total-row final">
                <span>Order Total:</span>
                <span>${{ number_format($order->total, 2) }}</span>
            </div>
        @endif
    </div>
    
    <div style="text-align: center; margin: 30px 0; padding: 20px; background-color: #ffffff; border: 2px solid #fed7d7; border-left: 4px solid #f56565; border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">
        <h4 style="color: #2d3748 !important; font-size: 16px !important; font-weight: 600 !important; margin: 0 0 10px 0;">⏰ Next Steps</h4>
        <p style="margin: 0; color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important; line-height: 1.5;">Please process this order as soon as possible to ensure timely delivery.</p>
    </div>
    
    <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important; margin-top: 30px;">
        <em style="color: #4a5568 !important; font-style: italic !important;">This is an automated notification. Please do not reply to this email.</em>
    </p>
@endsection