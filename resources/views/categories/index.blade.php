@extends('layouts.main')

@section('title', 'IQOS TEREA Sticks Categories USA | Buy Premium Tobacco Online')
@section('description', 'Browse all IQOS TEREA sticks categories in USA. Wide selection of premium tobacco products with authentic flavors, fast USPS shipping and competitive prices.')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Заголовок страницы -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">IQOS TEREA Sticks Categories - Buy Premium Tobacco USA</h1>
        <p class="text-gray-600">Choose a category to browse authentic IQOS TEREA sticks with fast shipping across USA</p>
        <div class="mt-4 text-gray-700">
            <p>Discover our premium collection of IQOS TEREA sticks available for purchase in the United States. We offer authentic tobacco products with competitive prices, fast USPS shipping, and excellent customer service. All TEREA sticks are genuine and sourced directly from authorized distributors.</p>
        </div>
    </div>
    
    <!-- Список категорий -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach($categories as $category)
        <a href="{{ route('categories.show', $category) }}" class="group">
            <div class="bg-white rounded-lg shadow-md overflow-hidden transition-transform transform hover:scale-105">
                <div class="h-48 overflow-hidden">
                    <img src="{{ $category->image_url }}" alt="{{ $category->name }} IQOS TEREA Sticks Category - Premium Tobacco USA" class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-lg text-center">{{ $category->name }}</h3>
                    <p class="text-sm text-gray-500 text-center mt-1">{{ $category->products_count }} товаров</p>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endsection
