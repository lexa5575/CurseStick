@extends('emails.layouts.modern')

@section('title', 'Order Confirmation - CruseStick')

@section('header', 'Thank You for Your Order!')

@section('content')
    <h2 style="color: #2d3748 !important; font-size: 22px !important; font-weight: 600 !important;">Order Confirmation #{{ $order->id }}</h2>
    
    <p style="color: #4a5568 !important; font-size: 16px !important; font-weight: normal !important;">Dear {{ $order->name }},</p>
    
    <p style="color: #4a5568 !important; font-size: 16px !important; font-weight: normal !important;">Thank you for your order! We're excited to get your products to you. Here are the details of your purchase:</p>
    
    <div class="info-box">
        <h4 style="color: #2d3748 !important; font-size: 16px !important; font-weight: 600 !important;">Order Information</h4>
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Order Number:</strong> #{{ $order->id }}</p>
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Order Date:</strong> {{ $order->created_at->format('F j, Y \a\t g:i A') }} UTC</p>
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Payment Status:</strong> {{ ucfirst($order->payment_status) }}</p>
    </div>
    
    @if($order->payment_method === 'crypto')
        <div class="info-box success">
            <h4 style="color: #2d3748 !important; font-size: 16px !important; font-weight: 600 !important;">Cryptocurrency Payment</h4>
            <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;">Your payment has been successfully received and processed via cryptocurrency.</p>
            <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Amount Paid:</strong> ${{ number_format($order->total, 2) }}</p>
            <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Status:</strong> <span style="color: #22c55e; font-weight: bold;">Paid</span></p>
        </div>
    @elseif($order->payment_method === 'zelle')
        @if(isset($zelleAddress))
        <div class="info-box warning">
            <h4 style="color: #2d3748 !important; font-size: 16px !important; font-weight: 600 !important;">Zelle Payment Instructions</h4>
            <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;">To complete your order, please send <strong style="color: #2d3748 !important; font-weight: 600 !important;">${{ number_format($order->total, 2) }}</strong>
                @if(isset($calculation) && isset($appliedCoupons) && !empty($appliedCoupons))
                    <span style="color: #22c55e;">(with {{ $appliedCoupons[0]->code }} coupon discount applied)</span>
                @endif
                to the following Zelle address:</p>
            <div style="background-color: white; padding: 15px; border-radius: 6px; border: 2px dashed #4299e1; font-family: monospace; font-size: 20px; font-weight: bold; text-align: center; margin: 15px 0; color: #000000;">
                {{ $zelleAddress->address }}
            </div>
            <p style="color: #4a5568 !important; font-size: 12px !important; font-weight: normal !important;"><small>Registered email: {{ $zelleAddress->email }}</small></p>
            
            @if($zelleAddress->note)
            <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Note:</strong> {{ $zelleAddress->note }}</p>
            @endif
            
            <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Important:</strong> When making the payment, please include <strong style="color: #2d3748 !important; font-weight: 600 !important;">service</strong> in the payment comment.</p>
        </div>
        @else
        <div class="info-box warning">
            <h4 style="color: #2d3748 !important; font-size: 16px !important; font-weight: 600 !important;">Zelle Payment Information</h4>
            <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;">You have selected Zelle as your payment method. You will receive another email shortly with the payment details and instructions.</p>
            <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Total Amount:</strong> ${{ number_format($order->total, 2) }}
                @if(isset($calculation) && isset($appliedCoupons) && !empty($appliedCoupons))
                    <span style="color: #22c55e;">(with {{ $appliedCoupons[0]->code }} coupon discount applied)</span>
                @endif
            </p>
            <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Important:</strong> When making the payment, please include <strong style="color: #2d3748 !important; font-weight: 600 !important;">service</strong> in the payment comment.</p>
        </div>
        @endif
    @endif
    
    <h3 style="color: #2d3748 !important; font-size: 18px !important; font-weight: 600 !important;">Shipping Address</h3>
    <div class="shipping-address">
        <h4 style="color: #2d3748 !important; font-size: 16px !important; font-weight: 600 !important;">Delivery Information</h4>
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;">
            {{ $order->name }}<br>
            @if($order->company){{ $order->company }}<br>@endif
            {{ $order->street }}@if($order->house), {{ $order->house }}@endif<br>
            {{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}<br>
            {{ $order->country }}
        </p>
        @if($order->phone)
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Phone:</strong> {{ $order->phone }}</p>
        @endif
    </div>
    
    <h3 style="color: #2d3748 !important; font-size: 18px !important; font-weight: 600 !important;">Order Items</h3>
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
        @if(isset($calculation) && isset($appliedCoupons) && !empty($appliedCoupons))
            <div class="total-row">
                <span>Subtotal:</span>
                <span>${{ number_format($calculation['original_total'], 2) }}</span>
            </div>
            @foreach($appliedCoupons as $coupon)
                <div class="total-row" style="color: #22c55e;">
                    <span>Coupon {{ $coupon->code }} Discount:</span>
                    <span>-${{ number_format($calculation['total_discount'], 2) }}</span>
                </div>
            @endforeach
            <div class="total-row final">
                <span>Total:</span>
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
                            <span>Coupon {{ $couponCode }} Discount:</span>
                            <span>-${{ number_format($discountAmount, 2) }}</span>
                        </div>
                    @endif
                @endforeach
            @endif
            <div class="total-row final">
                <span>Total:</span>
                <span>${{ number_format($order->total, 2) }}</span>
            </div>
        @else
            <div class="total-row final">
                <span>Total:</span>
                <span>${{ number_format($order->total, 2) }}</span>
            </div>
        @endif
    </div>
    
    @if($order->comment)
    <div class="info-box">
        <h4 style="color: #2d3748 !important; font-size: 16px !important; font-weight: 600 !important;">Order Notes</h4>
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;">{{ $order->comment }}</p>
    </div>
    @endif
    
    <div style="text-align: center; margin: 30px 0; padding: 20px; background-color: #ffffff; border: 2px solid #bee3f8; border-left: 4px solid #4299e1; border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">
        <h4 style="font-size: 16px !important; color: #2d3748 !important; margin: 0 0 20px 0; font-weight: 600 !important;">
            What's Next?
        </h4>
        @if($order->payment_method === 'crypto' && $order->payment_status === 'completed')
            <p style="margin: 8px 0; color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;">✅ Your payment has been confirmed</p>
            <p style="margin: 8px 0; color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;">📦 Your order is being processed and will ship within 24-48 hours</p>
            <p style="margin: 8px 0; color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;">📧 You'll receive tracking information via email once shipped</p>
        @else
            <p style="margin: 8px 0; color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;">📧 Complete your payment using the instructions above</p>
            <p style="margin: 8px 0; color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;">⏳ Once payment is confirmed, we'll process your order</p>
            <p style="margin: 8px 0; color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;">📦 Your items will ship within 24-48 hours after payment</p>
            <p style="margin: 8px 0; color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;">🚚 Tracking information will be sent to your email</p>
        @endif
    </div>
    
    <p style="color: #4a5568 !important; font-size: 16px !important; font-weight: normal !important;">If you have any questions about your order, please don't hesitate to contact our support team.</p>
    
    <p style="margin-top: 30px; color: #4a5568 !important; font-size: 16px !important; font-weight: normal !important;">
        Best regards,<br>
        <strong style="color: #2d3748 !important; font-weight: 600 !important;">The CruseStick Team</strong>
    </p>
@endsection