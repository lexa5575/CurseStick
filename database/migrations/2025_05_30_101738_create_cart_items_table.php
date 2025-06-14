<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            
            // Полиморфная связь
            $table->morphs('itemable');
            
            // Безопасное количество
            $table->unsignedTinyInteger('quantity')->default(1);
            
            // Безопасная цена
            $table->decimal('price', 10, 2)->unsigned();
            
            // JSON для опций
            $table->json('options')->nullable();
            
            $table->timestamps();
            
            // Индексы
            $table->index('cart_id');
            $table->index(['itemable_id', 'itemable_type']);
            
            // Уникальность
            $table->unique(['cart_id', 'itemable_id', 'itemable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};