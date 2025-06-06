@extends('layouts.main')

@section('title', $category->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Заголовок категории -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">{{ $category->name }}</h1>
        <p class="text-gray-600">{{ $category->description }}</p>
    </div>
    
    <!-- Товары категории -->
    <div class="mb-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($products as $product)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <a href="{{ route('products.show', $product) }}" class="block">
                    <div class="relative h-48 overflow-hidden">
                        <img src="{{ $product->image_url ?? asset('images/placeholders/product-placeholder.jpg') }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
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
                            x-data="cartHandler"
                            x-on:click="addToCart({{ $product->id }}, 1)"
                            x-bind:disabled="loading"
                            x-bind:class="{'opacity-50': loading}"
                            class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-full transition-colors"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full py-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="text-xl font-semibold text-gray-500">There are no products in this category yet</h3>
                <p class="text-gray-500 mt-2">Check back later or browse other categories</p>
            </div>
            @endforelse
        </div>
    </div>
    
    <!-- Другие категории -->
    @if($otherCategories && $otherCategories->count() > 0)
    <div class="mb-12">
        <h2 class="text-2xl font-bold mb-6">Other Categories</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            @foreach($otherCategories as $otherCategory)
            <a href="{{ route('categories.show', $otherCategory) }}" class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transition-shadow">
                <h3 class="font-semibold">{{ $otherCategory->name }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ $otherCategory->products_count }} products</p>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection

<!-- Подключение компонента корзины -->
<script src="{{ asset('js/cartHandler.js') }}" defer></script>
