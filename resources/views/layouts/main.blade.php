<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CruseStick') }}</title>

    <!-- Стили и скрипты -->
    <link rel="stylesheet" href="{{ asset('build/assets/app.css') }}">
    <script src="{{ asset('build/assets/app2.js') }}" type="module"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Дополнительные стили -->
    <style>
        [x-cloak] { display: none !important; }
        
        .category-dropdown-item {
            display: block;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            color: #374151;
            transition: background-color 0.2s;
        }
        
        .category-dropdown-item:hover {
            background-color: #f3f4f6;
            color: #1d4ed8;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Шапка сайта -->
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <!-- Логотип -->
                <a href="{{ route('home') }}" class="text-xl font-bold text-blue-600">
                    CruseStick
                </a>

                <!-- Навигация -->
                <nav class="hidden md:flex items-center space-x-4">
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>

                    <!-- Выпадающее меню категорий -->
                    <div class="relative" x-data="{ open: false }">
                        <a href="{{ route('categories.index') }}"
                           @mouseenter="open = true"
                           class="nav-link {{ request()->routeIs('categories.index') ? 'active' : '' }} flex items-center">
                            <span>Categories</span>
                            <svg class="ml-1 h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        
                        <!-- Выпадающее меню категорий -->
                        <div 
                            x-show="open" 
                            @click.away="open = false"
                            @mouseleave="open = false" 
                            class="absolute left-0 top-full mt-1 w-48 bg-white rounded-md shadow-lg py-1 z-50"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-1"
                            x-cloak
                        >
                            @foreach($categories ?? [] as $category)
                            <a href="{{ route('categories.show', $category) }}" class="category-dropdown-item">
                                {{ $category->name }}
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Ссылка на корзину с счетчиком -->
                    <a href="{{ route('cart.index') }}" class="nav-link {{ request()->routeIs('cart.index') ? 'active' : '' }} flex items-center">
                        <span class="mr-1">Cart</span>
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="cart-counter absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center hidden"></span>
                        </div>
                    </a>

                    @auth
                    {{-- Пункты меню для авторизованных пользователей можно добавить здесь --}}
                    @endauth
                    <a href="{{ route('faq') }}" class="nav-link {{ request()->routeIs('faq') ? 'active' : '' }}">FAQ</a>
                </nav>

                <!-- Мобильное меню -->
                @include('partials.mobile-menu')
            </div>
        </div>
    </header>


    <!-- Основное содержимое -->
    <main>
        @yield('content')
        @if(isset($content))
            {!! $content !!}
        @endif
    </main>

    <!-- Футер -->
    <footer class="bg-gray-800 text-white mt-8">
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- О нас -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">About Us</h3>
                    <p class="text-gray-400">CruseStick - an online store of quality products at affordable prices. A wide range of products.</p>
                </div>

                <!-- Информация -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Information</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="footer-link">Shipping & Payment</a></li>
                        <li><a href="#" class="footer-link">Returns</a></li>
                    </ul>
                </div>

                <!-- Контакты -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact Us</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="footer-link">Get in Touch</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400 text-sm">
                <p>© {{ date('Y') }} CruseStick. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <!-- Alpine.js уже обрабатывает все необходимые интеракции для выпадающего меню -->

</body>
</html>
