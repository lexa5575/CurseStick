@extends('layouts.main')

@section('title', 'Payment Cancelled')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-2xl mx-auto text-center">
        <!-- Cancel Icon -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-yellow-100 rounded-full">
                <svg class="w-12 h-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
        </div>

        <!-- Cancel Message -->
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Payment Cancelled</h1>
        <p class="text-lg text-gray-600 mb-8">
            Your payment has been cancelled. No charges have been made to your account.
        </p>

        @if($order)
        <div class="bg-gray-100 rounded-lg p-6 mb-8">
            <p class="text-sm text-gray-600 mb-2">Order ID</p>
            <p class="text-lg font-semibold text-gray-900">#{{ $order->id }}</p>
            <p class="text-sm text-gray-500 mt-2">Your order has been cancelled and will not be processed.</p>
        </div>
        @endif

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('cart.index') }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Return to Cart
            </a>
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-md shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Continue Shopping
            </a>
        </div>

        <!-- Help Text -->
        <p class="mt-8 text-sm text-gray-500">
            If you experienced any issues during the payment process, please 
            <a href="{{ route('faq') }}" class="text-blue-600 hover:text-blue-700 underline">contact our support team</a>.
        </p>
    </div>
</div>
@endsection 