@extends('layouts.main')

@section('title', 'Payment Failed | IQOS TEREA Order Issue')
@section('description', 'Payment for your IQOS TEREA sticks order failed. Please try again or contact support. We accept multiple payment methods for your convenience.')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-2xl mx-auto text-center">
        <!-- Failed Payment Icon -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-red-100 rounded-full">
                <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
        </div>

        <!-- Failed Payment Message -->
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Payment Failed</h1>
        <p class="text-lg text-gray-600 mb-8">
            Unfortunately, your payment could not be processed. This may be due to insufficient funds, network issues, or payment timeout.
        </p>

        @if($order)
        <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-8">
            <p class="text-sm text-gray-600 mb-2">Order ID</p>
            <p class="text-lg font-semibold text-gray-900 mb-4">#{{ $order->id }}</p>
            
            <div class="text-left space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Amount:</span>
                    <span class="font-medium">${{ number_format($order->total, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Status:</span>
                    <span class="font-medium text-red-600">Payment Failed</span>
                </div>
            </div>
        </div>
        @endif

        <!-- What to do next -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-blue-900 mb-3">What can you do?</h3>
            <div class="text-blue-700 text-left space-y-2">
                <p>• Check your wallet balance and try again</p>
                <p>• Verify your internet connection is stable</p>
                <p>• Try using a different cryptocurrency</p>
                <p>• Contact support if the issue persists</p>
            </div>
        </div>

        <!-- Important Notice -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-8">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="text-left">
                    <p class="text-yellow-800 font-medium">Important Notice</p>
                    <p class="text-yellow-700 text-sm mt-1">
                        No charges have been made to your account. You can safely try placing your order again.
                    </p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('cart.index') }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Try Again
            </a>
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-md shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Continue Shopping
            </a>
        </div>

        <!-- Support Help -->
        <div class="mt-8 bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-600 mb-2">
                <strong>Still having trouble?</strong>
            </p>
            <p class="text-sm text-gray-500">
                Use our 
                <a href="{{ route('faq') }}" class="text-blue-600 hover:text-blue-700 underline font-medium">contact form</a> 
                to reach our support team. Please include your Order ID <strong>#{{ $order->id ?? 'N/A' }}</strong> in your message.
            </p>
        </div>
    </div>
</div>
@endsection