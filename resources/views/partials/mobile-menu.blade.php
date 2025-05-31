<!-- Мобильное меню -->
<div class="md:hidden" x-data="{ open: false }">
    <button @click="open = !open" class="text-gray-500 hover:text-gray-700 focus:outline-none">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div x-show="open" @click.away="open = false" class="absolute top-16 right-0 bg-white shadow-lg rounded-lg py-2 w-48 z-10">
        <a href="{{ route('home') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Главная</a>
        <a href="{{ route('categories.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Категории</a>
        <a href="{{ route('cart.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Корзина</a>
        @auth
        <a href="{{ route('favorites.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Избранное</a>
        <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Заказы</a>
        @endauth
        <a href="{{ route('faq') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">FAQ</a>
    </div>
</div>
