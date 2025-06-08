<!-- Мобильное меню -->
<div class="md:hidden flex items-center gap-4">
    <!-- Корзина для мобильных устройств -->
    <a href="{{ route('cart.index') }}" class="relative">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <span class="cart-counter absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center hidden"></span>
    </a>

    <!-- Бургер меню -->
    <div x-data="{ open: false }">
        <button @click="open = !open" class="text-gray-500 hover:text-gray-700 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <div x-show="open" @click.away="open = false" class="absolute top-16 right-0 bg-white shadow-lg rounded-lg py-2 w-48 z-10">
            <a href="{{ route('home') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Home</a>
            <a href="{{ route('categories.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Categories</a>
            <a href="{{ route('faq') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">FAQ</a>
        </div>
    </div>
</div>
