@extends('layouts.main')

@section('title', 'Buy IQOS TEREA Sticks USA | CruseStick - Best Prices & Fast Shipping')
@section('description', 'Buy authentic IQOS TEREA sticks online in USA. Premium tobacco products, competitive prices, fast USPS shipping. Wide selection of TEREA flavors available.')

@section('content')
<!-- Баннер-слайдер -->
@include('partials.banner-slider')

<div class="container mx-auto px-4 py-8">
    <!-- Категории товаров -->
    <div class="mb-12">
        <h2 class="text-2xl font-bold mb-6">Popular IQOS TEREA Categories</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($categories as $category)
            <a href="{{ route('categories.show', $category) }}" class="group">
                <div class="bg-white rounded-lg shadow-md overflow-hidden transition-transform transform hover:scale-105">
                    <div class="h-48 overflow-hidden">
                        <img src="{{ $category->image_url }}" alt="{{ $category->name }} IQOS TEREA Sticks Category - Buy Premium Tobacco USA" class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-lg text-center">{{ $category->name }}</h3>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    
    <!-- Популярные товары -->
    <div>
        <h2 class="text-2xl font-bold mb-6">Featured IQOS TEREA Sticks</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($featuredProducts as $product)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <a href="{{ route('products.show', $product) }}" class="block">
                    <div class="relative h-48 overflow-hidden">
                        <img src="{{ $product->image_url }}" alt="Buy {{ $product->name }} IQOS TEREA Sticks USA - Premium Tobacco" class="w-full h-full object-cover">
                        @if((float)$product->discount > 0)
                        <div class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                            -{{ number_format($product->discount / $product->price * 100) }}%
                        </div>
                        @endif
                    </div>
                </a>
                <div class="p-4">
                    <a href="{{ route('products.show', $product) }}" class="block">
                        <h3 class="font-semibold text-lg mb-2 hover:text-blue-600 transition-colors">{{ $product->name }}</h3>
                    </a>
                    <div class="flex justify-between items-center">
                        <div>
                            @if((float)$product->discount > 0)
                            <span class="text-gray-400 line-through text-sm">{{ number_format($product->price, 2) }} $</span>
                            <span class="text-lg font-bold text-red-600">{{ number_format($product->price - $product->discount, 2) }} $</span>
                            @else
                            <span class="text-lg font-bold">{{ number_format($product->price, 2) }} $</span>
                            @endif
                        </div>
                        <button 
                            type="button"
                            class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-full transition-colors"
                            title="Add to Cart"
                            x-data="cartHandler"
                            x-on:click="addToCart({{ $product->id }}, 1)"
                            x-bind:disabled="loading"
                            x-bind:class="{'opacity-50': loading}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    </div>
</div>
@endsection

<!-- Подключение компонента корзины -->
<script src="{{ asset('js/cartHandler.js') }}" defer></script>
