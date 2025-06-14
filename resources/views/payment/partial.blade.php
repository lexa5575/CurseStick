@extends('layouts.main')

@section('title', 'Partial Payment Received | IQOS TEREA Order Pending')
@section('description', 'Partial payment received for your IQOS TEREA sticks order. Complete remaining payment to process your order and begin shipping.')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-2xl mx-auto text-center">
        <!-- Partial Payment Icon -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-orange-100 rounded-full">
                <svg class="w-12 h-12 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
            </div>
        </div>

        <!-- Partial Payment Message -->
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Partial Payment Received</h1>
        <p class="text-lg text-gray-600 mb-8">
            We've received a partial payment for your order. The remaining balance needs to be settled to complete your purchase.
        </p>

        @if($order)
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-6 mb-8">
            <p class="text-sm text-gray-600 mb-2">Order ID</p>
            <p class="text-lg font-semibold text-gray-900 mb-4">#{{ $order->id }}</p>
            
            <div class="text-left space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Amount:</span>
                    <span class="font-medium">${{ number_format($order->total, 2) }}</span>
                </div>
                @if(isset($actuallyPaid))
                <div class="flex justify-between">
                    <span class="text-gray-600">Amount Received:</span>
                    <span class="font-medium text-orange-600">${{ number_format($actuallyPaid, 2) }}</span>
                </div>
                <div class="flex justify-between border-t pt-2">
                    <span class="text-gray-600 font-medium">Remaining Balance:</span>
                    <span class="font-bold text-red-600">${{ number_format($order->total - $actuallyPaid, 2) }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Support Information -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-blue-900 mb-3">What happens next?</h3>
            <div class="text-blue-700 text-left space-y-2">
                <p>• Our support team has been notified about your partial payment</p>
                <p>• We will contact you within 24 hours to arrange payment of the remaining balance</p>
                <p>• Your order will be processed once the full payment is received</p>
            </div>
        </div>

        <!-- Contact Support Section -->
        <div class="bg-gray-100 rounded-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Need immediate assistance?</h3>
            <p class="text-gray-600 mb-4">
                Use our contact form to reach our support team with your order details for faster processing.
            </p>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <p class="text-blue-700 text-sm">
                    <strong>Important:</strong> When filling out the contact form, please include your Order ID <strong>#{{ $order->id ?? 'N/A' }}</strong> in your message for quick reference.
                </p>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-md shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Continue Shopping
            </a>
            <a href="{{ route('faq') }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Contact Support Form
            </a>
        </div>
    </div>
</div>
@endsection