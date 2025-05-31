<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            // Заменяем product_id на полиморфную связь
            $table->morphs('itemable'); // Создает itemable_id и itemable_type поля
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->json('options')->nullable();
            $table->timestamps();
            
            // Индексы для быстрого доступа
            $table->index('cart_id');
            $table->index('itemable_id');
            $table->index('itemable_type');
            
            // Уникальный индекс для предотвращения дубликатов одного элемента в корзине
            $table->unique(['cart_id', 'itemable_id', 'itemable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
