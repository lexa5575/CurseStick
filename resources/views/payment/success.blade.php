@extends('layouts.main')

@section('title', 'Payment Successful')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-2xl mx-auto text-center">
        <!-- Success Icon -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-green-100 rounded-full">
                <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>

        <!-- Success Message -->
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Payment Successful!</h1>
        <p class="text-lg text-gray-600 mb-8">
            Thank you for your payment. Your transaction has been completed successfully.
        </p>

        <!-- Order Info -->
        @if($order)
        <div class="bg-gray-100 rounded-lg p-6 mb-8">
            <p class="text-sm text-gray-600 mb-2">Order ID</p>
            <p class="text-lg font-semibold text-gray-900 mb-4">#{{ $order->id }}</p>
            
            <div class="text-left space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Amount:</span>
                    <span class="font-medium">${{ number_format($order->total, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Payment Method:</span>
                    <span class="font-medium">Cryptocurrency</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Status:</span>
                    <span class="font-medium text-green-600">{{ $order->status }}</span>
                </div>
            </div>
        </div>
        
        <p class="text-gray-600 mb-6">
            We've sent a confirmation email to <strong>{{ $order->email }}</strong> with your order details.
        </p>
        @endif

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-md shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Continue Shopping
            </a>
            @if($order)
            <a href="{{ route('orders.confirmation', $order->id) }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                View Order Details
            </a>
            @endif
        </div>
    </div>
</div>
@endsection 