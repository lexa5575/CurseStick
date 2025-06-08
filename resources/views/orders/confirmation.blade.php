@extends('layouts.main')

@section('title', 'Order Confirmation')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md overflow-hidden p-6">
        <div class="text-center mb-8">
            <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <h1 class="text-3xl font-bold text-gray-900">Order Placed Successfully!</h1>
            <p class="text-lg text-gray-600 mt-2">Thank you for your order #{{ $order->id }}</p>
            
            {{-- Payment status indicator for crypto payments --}}
            @if($order->payment_method === 'crypto' && $order->payment_status === 'completed')
            <div class="mt-4 inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-full">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                Payment Confirmed
            </div>
            @endif
        </div>

        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">What's Next?</h2>
            <div class="space-y-3 text-gray-700">
                @if($order->payment_method === 'crypto' && $order->payment_status === 'completed')
                    {{-- Instructions for paid crypto orders --}}
                    <p><span class="font-medium">1.</span> Your payment has been successfully received and confirmed.</p>
                    <p><span class="font-medium">2.</span> Your order is now being processed and will be shipped within 24-48 hours.</p>
                    <p><span class="font-medium">3.</span> You will receive an email with tracking information once your order ships.</p>
                    <p><span class="font-medium">4.</span> If you have any questions, please contact our support team.</p>
                @else
                    {{-- Instructions for other payment methods (Zelle, etc.) --}}
                    <p><span class="font-medium">1.</span> An email with order details and payment instructions has been sent to <span class="font-semibold">{{ $order->email }}</span>.</p>
                    <p><span class="font-medium">2.</span> Please follow the payment instructions for your selected payment method: <span class="font-semibold">{{ ucfirst($order->payment_method) }}</span>.</p>
                    <p><span class="font-medium">3.</span> Once your payment is confirmed, we will process your order and ship it.</p>
                    <p><span class="font-medium">4.</span> A tracking number will be emailed to you when your order ships.</p>
                @endif
            </div>
        </div>

        {{-- Estimated delivery for paid crypto orders --}}
        @if($order->payment_method === 'crypto' && $order->payment_status === 'completed')
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-2">Estimated Delivery</h3>
            <p class="text-blue-700">
                Your order will be shipped within 24-48 hours. Estimated delivery time is 5-7 business days after shipping.
            </p>
        </div>
        @endif

        <div class="border-t border-gray-200 pt-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Order Summary</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($order->items as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-md object-cover" src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($item->price, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($item->quantity * $item->price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Total:</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${{ number_format($order->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6 mt-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Shipping Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-gray-600"><span class="font-medium text-gray-800">Name:</span> {{ $order->name }}</p>
                    @if($order->company)
                    <p class="text-gray-600"><span class="font-medium text-gray-800">Company:</span> {{ $order->company }}</p>
                    @endif
                    <p class="text-gray-600"><span class="font-medium text-gray-800">Email:</span> {{ $order->email }}</p>
                    <p class="text-gray-600"><span class="font-medium text-gray-800">Phone:</span> {{ $order->phone }}</p>
                </div>
                <div>
                    <p class="text-gray-600"><span class="font-medium text-gray-800">Address:</span> {{ $order->street }}</p>
                    @if($order->address_unit)
                    <p class="text-gray-600"><span class="font-medium text-gray-800">Apt/Suite:</span> {{ $order->address_unit }}</p>
                    @endif
                    <p class="text-gray-600"><span class="font-medium text-gray-800">City:</span> {{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}</p>
                    <p class="text-gray-600"><span class="font-medium text-gray-800">Country:</span> {{ $order->country }}</p>
                </div>
            </div>
            
            @if($order->comment)
            <div class="mt-4">
                <p class="font-medium text-gray-800">Order Notes:</p>
                <p class="text-gray-600">{{ $order->comment }}</p>
            </div>
            @endif
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                Continue Shopping
            </a>
        </div>
    </div>
</div>
@endsection

