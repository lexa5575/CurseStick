@extends('layouts.main')

@section('title', 'FAQ')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold text-center mb-8">Frequently Asked Questions (FAQ)</h1>

        <div class="space-y-6">
            <!-- Question 1 -->
            <div x-data="{ open: false }" class="bg-white rounded-lg shadow-md transform transition-all duration-200 hover:scale-105">
                <button @click="open = !open" class="w-full flex justify-between items-center text-left text-lg font-semibold p-6 focus:outline-none">
                    <span>How quickly do you ship orders?</span>
                    <svg :class="{'transform rotate-180': open}" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-cloak x-transition class="p-6 pt-0 text-gray-700">
                    <p>We ship all orders within 1–2 business days after payment is confirmed.</p>
                </div>
            </div>

            <!-- Question 2 -->
            <div x-data="{ open: false }" class="bg-white rounded-lg shadow-md transform transition-all duration-200 hover:scale-105">
                <button @click="open = !open" class="w-full flex justify-between items-center text-left text-lg font-semibold p-6 focus:outline-none">
                    <span>Which shipping service do you use?</span>
                    <svg :class="{'transform rotate-180': open}" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-cloak x-transition class="p-6 pt-0 text-gray-700">
                    <p>All packages are shipped via USPS. Delivery typically takes between 2 to 5 business days.</p>
                </div>
            </div>

            <!-- Question 3 -->
            <div x-data="{ open: false }" class="bg-white rounded-lg shadow-md transform transition-all duration-200 hover:scale-105">
                <button @click="open = !open" class="w-full flex justify-between items-center text-left text-lg font-semibold p-6 focus:outline-none">
                    <span>Where are you located and where do you ship?</span>
                    <svg :class="{'transform rotate-180': open}" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-cloak x-transition class="p-6 pt-0 text-gray-700">
                    <p>We ship exclusively within the United States. International shipping is not available at this time.</p>
                </div>
            </div>
            
            <!-- Question 4 -->
            <div x-data="{ open: false }" class="bg-white rounded-lg shadow-md transform transition-all duration-200 hover:scale-105">
                <button @click="open = !open" class="w-full flex justify-between items-center text-left text-lg font-semibold p-6 focus:outline-none">
                    <span>Can I return a product?</span>
                    <svg :class="{'transform rotate-180': open}" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-cloak x-transition class="p-6 pt-0 text-gray-700">
                    <p>We generally do not accept returns, as our products are not eligible for return once opened.</p>
                    <p class="mt-3">However, if you received the wrong item or a damaged product, please contact us. In rare cases, a return may be approved — but only if the product is unused, in its original sealed packaging, and can be resold.</p>
                    <p class="mt-3 font-semibold">Important: Do not return the product without contacting us first. Unauthorized returns cannot be accepted. If your return is approved, we will provide a return label. The product must be packed in neutral packaging — either the original box or a plain one that does not reveal the contents.</p>
                </div>
            </div>

            <!-- Question 5 -->
            <div x-data="{ open: false }" class="bg-white rounded-lg shadow-md transform transition-all duration-200 hover:scale-105">
                <button @click="open = !open" class="w-full flex justify-between items-center text-left text-lg font-semibold p-6 focus:outline-none">
                    <span>What if my tracking number doesn't update?</span>
                    <svg :class="{'transform rotate-180': open}" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-cloak x-transition class="p-6 pt-0 text-gray-700">
                    <p>Please allow some time — USPS tracking updates can sometimes be delayed. Occasionally, technical issues in their system may cause longer delays. If your tracking status hasn't updated within 24–48 hours, feel free to contact our support team for assistance.</p>
                </div>
            </div>

        </div>

        <!-- Contact Form Section -->
        <div id="contact-form" class="mt-16 bg-white rounded-lg shadow-md p-8">
            <h2 class="text-3xl font-bold text-center mb-6">Still have questions?</h2>
            <p class="text-center text-gray-600 mb-8">If you cannot find an answer to your question in our FAQ, you can always contact us. We will answer you shortly!</p>

            {{-- Success Message --}}
            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                </span>
            </div>
            @endif

            {{-- Error Message --}}
            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                </span>
            </div>
            @endif

            {{-- Validation Errors --}}
            @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Please fix the following errors:</strong>
                <ul class="list-disc list-inside mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('contact.send') }}" method="POST">
                @csrf
                
                {{-- Honeypot field for bot protection --}}
                <div style="position: absolute; left: -5000px;" aria-hidden="true">
                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                </div>
                
                <!-- Name and Email Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" id="name" autocomplete="name" required value="{{ old('name') }}" oninvalid="this.setCustomValidity('Please enter your name')" oninput="this.setCustomValidity('')" class="mt-2 block w-full rounded-xl border-4 border-blue-600 bg-gray-50 shadow-2xl focus:border-blue-700 focus:ring-4 focus:ring-blue-200 text-lg py-4 px-5 transition-all duration-200 hover:border-blue-400 transform hover:scale-105 @error('name') border-red-500 @enderror" placeholder="John Doe">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" autocomplete="email" required value="{{ old('email') }}" oninvalid="this.setCustomValidity('Please enter a valid email address')" oninput="this.setCustomValidity('')" class="mt-2 block w-full rounded-xl border-4 border-blue-600 bg-gray-50 shadow-2xl focus:border-blue-700 focus:ring-4 focus:ring-blue-200 text-lg py-4 px-5 transition-all duration-200 hover:border-blue-400 transform hover:scale-105 @error('email') border-red-500 @enderror" placeholder="you@example.com">
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Title Field -->
                <div class="mt-6">
                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" class="mt-2 block w-full rounded-xl border-4 border-blue-600 bg-gray-50 shadow-2xl focus:border-blue-700 focus:ring-4 focus:ring-blue-200 text-lg py-4 px-5 transition-all duration-200 hover:border-blue-400 transform hover:scale-105" placeholder="e.g., Question about my order">
                </div>

                <!-- Question Topic Field -->
                <div class="mt-6">
                    <label for="question_topic" class="block text-sm font-medium text-gray-700">Your Question</label>
                    <textarea id="question_topic" name="question_topic" rows="5" required minlength="20" maxlength="1200" oninvalid="this.setCustomValidity('Your question must be between 20 and 1200 characters')" oninput="this.setCustomValidity('')" class="mt-2 block w-full rounded-xl border-4 border-blue-600 bg-gray-50 shadow-2xl focus:border-blue-700 focus:ring-4 focus:ring-blue-200 text-lg py-4 px-5 transition-all duration-200 resize-none hover:border-blue-400 transform hover:scale-105 @error('question_topic') border-red-500 @enderror" style="resize: none;" placeholder="Enter your question here...">{{ old('question_topic') }}</textarea>
                    @error('question_topic')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="mt-8 text-center">
                    <button type="submit" class="inline-flex justify-center items-center rounded-full bg-blue-600 py-3 px-16 min-w-[220px] text-lg font-bold text-white shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide success/error messages after 10 seconds
    const alerts = document.querySelectorAll('[role="alert"]');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s ease-in-out';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        }, 10000);
        
        // Add click handler to close button
        const closeBtn = alert.querySelector('svg[role="button"]');
        if (closeBtn) {
            closeBtn.parentElement.style.cursor = 'pointer';
            closeBtn.parentElement.addEventListener('click', function() {
                alert.style.transition = 'opacity 0.2s ease-in-out';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 200);
            });
        }
    });
});
</script>
@endsection 