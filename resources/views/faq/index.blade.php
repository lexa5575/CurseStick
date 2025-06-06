@extends('layouts.main')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold text-center mb-8">Frequently Asked Questions (FAQ)</h1>

        <div class="space-y-6">
            <!-- Question 1 -->
            <div x-data="{ open: false }" class="bg-white rounded-lg shadow-md transform transition-all duration-200 hover:scale-105">
                <button @click="open = !open" class="w-full flex justify-between items-center text-left text-lg font-semibold p-6 focus:outline-none">
                    <span>What payment methods do you accept?</span>
                    <svg :class="{'transform rotate-180': open}" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-cloak x-transition class="p-6 pt-0 text-gray-700">
                    <p>We accept payments via Zelle and various cryptocurrencies. You can select your preferred payment method during checkout.</p>
                </div>
            </div>

            <!-- Question 2 -->
            <div x-data="{ open: false }" class="bg-white rounded-lg shadow-md transform transition-all duration-200 hover:scale-105">
                <button @click="open = !open" class="w-full flex justify-between items-center text-left text-lg font-semibold p-6 focus:outline-none">
                    <span>What is your shipping policy?</span>
                    <svg :class="{'transform rotate-180': open}" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-cloak x-transition class="p-6 pt-0 text-gray-700">
                    <p>We offer shipping across the United States. Shipping times and costs may vary depending on your location. Detailed information will be provided at checkout once you enter your shipping address.</p>
                </div>
            </div>

            <!-- Question 3 -->
            <div x-data="{ open: false }" class="bg-white rounded-lg shadow-md transform transition-all duration-200 hover:scale-105">
                <button @click="open = !open" class="w-full flex justify-between items-center text-left text-lg font-semibold p-6 focus:outline-none">
                    <span>How can I track my order?</span>
                    <svg :class="{'transform rotate-180': open}" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-cloak x-transition class="p-6 pt-0 text-gray-700">
                    <p>Once your order is shipped, you will receive an email with a tracking number and a link to the carrier's website where you can track your package.</p>
                </div>
            </div>
            
            <!-- Question 4 -->
            <div x-data="{ open: false }" class="bg-white rounded-lg shadow-md transform transition-all duration-200 hover:scale-105">
                <button @click="open = !open" class="w-full flex justify-between items-center text-left text-lg font-semibold p-6 focus:outline-none">
                    <span>What is your return policy?</span>
                    <svg :class="{'transform rotate-180': open}" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-cloak x-transition class="p-6 pt-0 text-gray-700">
                    <p>If you are not satisfied with your purchase, you can return it within 30 days for a full refund or exchange. Please visit our 'Returns' page for more details and to initiate a return.</p>
                </div>
            </div>

        </div>

        <!-- Contact Form Section -->
        <div class="mt-16 bg-white rounded-lg shadow-md p-8">
            <h2 class="text-3xl font-bold text-center mb-6">Still have questions?</h2>
            <p class="text-center text-gray-600 mb-8">If you cannot find an answer to your question in our FAQ, you can always contact us. We will answer you shortly!</p>

            <form action="#" method="POST">
                @csrf
                <!-- Name and Email Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" id="name" autocomplete="name" required oninvalid="this.setCustomValidity('Please enter your name')" oninput="this.setCustomValidity('')" class="mt-2 block w-full rounded-xl border-4 border-blue-600 bg-gray-50 shadow-2xl focus:border-blue-700 focus:ring-4 focus:ring-blue-200 text-lg py-4 px-5 transition-all duration-200 hover:border-blue-400 transform hover:scale-105" placeholder="John Doe">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" autocomplete="email" required oninvalid="this.setCustomValidity('Please enter a valid email address')" oninput="this.setCustomValidity('')" class="mt-2 block w-full rounded-xl border-4 border-blue-600 bg-gray-50 shadow-2xl focus:border-blue-700 focus:ring-4 focus:ring-blue-200 text-lg py-4 px-5 transition-all duration-200 hover:border-blue-400 transform hover:scale-105" placeholder="you@example.com">
                    </div>
                </div>

                <!-- Title Field -->
                <div class="mt-6">
                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" name="title" id="title" class="mt-2 block w-full rounded-xl border-4 border-blue-600 bg-gray-50 shadow-2xl focus:border-blue-700 focus:ring-4 focus:ring-blue-200 text-lg py-4 px-5 transition-all duration-200 hover:border-blue-400 transform hover:scale-105" placeholder="e.g., Question about my order">
                </div>

                <!-- Question Topic Field -->
                <div class="mt-6">
                    <label for="question_topic" class="block text-sm font-medium text-gray-700">Your Question</label>
                    <textarea id="question_topic" name="question_topic" rows="5" required minlength="20" maxlength="1200" oninvalid="this.setCustomValidity('Your question must be between 20 and 1200 characters')" oninput="this.setCustomValidity('')" class="mt-2 block w-full rounded-xl border-4 border-blue-600 bg-gray-50 shadow-2xl focus:border-blue-700 focus:ring-4 focus:ring-blue-200 text-lg py-4 px-5 transition-all duration-200 resize-none hover:border-blue-400 transform hover:scale-105" style="resize: none;" placeholder="Enter your question here..."></textarea>
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
@endsection 