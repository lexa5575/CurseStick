@extends('layouts.main')

@section('title', $product->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="md:flex">
            <!-- Изображение товара -->
            <div class="md:w-1/2">
                <div class="h-96 overflow-hidden">
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                </div>
            </div>
            
            <!-- Информация о товаре -->
            <div class="p-8 md:w-1/2">
                <div class="uppercase tracking-wide text-sm text-blue-500 font-semibold">{{ $product->category->name }}</div>
                <h1 class="mt-2 text-3xl font-bold text-gray-900">{{ $product->name }}</h1>
                
                <div class="mt-4">
                    @if((float)$product->discount > 0)
                        <span class="text-gray-400 line-through text-lg">{{ number_format($product->price, 2) }} $</span>
                        <span class="text-2xl font-bold text-red-600 ml-2">{{ number_format($product->price - $product->discount, 2) }} $</span>
                        <span class="ml-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                            -{{ number_format($product->discount / $product->price * 100) }}%
                        </span>
                    @else
                        <span class="text-2xl font-bold text-gray-900">{{ number_format($product->price, 2) }} $</span>
                    @endif
                </div>
                
                <div class="mt-6 text-gray-600">
                    <p>{{ $product->description }}</p>
                </div>
                
                <!-- Счетчик количества и кнопка добавления в корзину -->
                <div class="mt-8" x-data="cartHandler">
                    <div class="flex items-center space-x-4" id="product-interaction-area">
                        <div class="flex items-center border border-gray-300 rounded">
                            <button 
                                type="button"
                                x-on:click="quantity > 1 ? quantity-- : null"
                                class="px-3 py-1 bg-gray-100 text-gray-600 hover:bg-gray-200 focus:outline-none"
                            >
                                -
                            </button>
                            <input 
                                type="text" 
                                x-model.number="quantity"
                                class="w-16 py-1 text-center focus:outline-none"
                                aria-label="Product quantity"
                                x-on:input="quantity = quantity ? (quantity < 1 ? 1 : parseInt(quantity)) : 1"
                            >
                            <button 
                                type="button"
                                x-on:click="quantity++"
                                class="px-3 py-1 bg-gray-100 text-gray-600 hover:bg-gray-200 focus:outline-none"
                            >
                                +
                            </button>
                        </div>
                        
                        <button 
                            type="button"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors flex items-center"
                            x-on:click="addToCart({{ $product->id }})"
                            x-bind:disabled="loading"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span x-text="loading ? 'Adding...' : 'Add to Cart'"></span>
                        </button>
                    </div>
                    
                    <!-- Сообщение об успехе -->
                    <div 
                        class="mt-4 p-3 bg-green-100 text-green-700 rounded transition-opacity duration-300"
                        x-show="showSuccess"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                    >
                        Product added to cart!
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Похожие товары -->
    @if($relatedProducts && $relatedProducts->count() > 0)
    <div class="mt-12">
        <h2 class="text-2xl font-bold mb-6">Related Products</h2>
        <div x-data="cartHandler" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($relatedProducts as $relatedProduct)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <a href="{{ route('products.show', $relatedProduct) }}">
                    <div class="relative h-48 overflow-hidden">
                        <img src="{{ $relatedProduct->image_url }}" alt="{{ $relatedProduct->name }}" class="w-full h-full object-cover">
                        @if((float)$relatedProduct->discount > 0)
                        <div class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                            -{{ number_format($relatedProduct->discount / $relatedProduct->price * 100) }}%
                        </div>
                        @endif
                    </div>
                </a>
                <div class="p-4">
                    <a href="{{ route('products.show', $relatedProduct) }}" class="block">
                        <h3 class="font-semibold text-lg mb-2 hover:text-blue-600 transition-colors">{{ $relatedProduct->name }}</h3>
                    </a>
                    <div class="flex justify-between items-center">
                        <div>
                            @if((float)$relatedProduct->discount > 0)
                            <span class="text-gray-400 line-through text-sm">{{ number_format($relatedProduct->price, 2) }} $</span>
                            <span class="text-lg font-bold text-red-600">{{ number_format($relatedProduct->price - $relatedProduct->discount, 2) }} $</span>
                            @else
                            <span class="text-lg font-bold">{{ number_format($relatedProduct->price, 2) }} $</span>
                            @endif
                        </div>
                        <button 
                            type="button"
                            class="bg-blue-500 hover:bg-blue-600 text-white rounded-full p-2 transition-colors"
                            x-on:click="addToCart({{ $relatedProduct->id }}, 1)"
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
    @endif
</div>
@endsection

<!-- Подключение компонента корзины -->
<script src="{{ asset('js/cartHandler.js') }}" defer></script>
