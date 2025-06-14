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
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            
            // ПРИМЕЧАНИЕ: user_id НЕ должен быть в $fillable модели (небезопасно!)
            // Устанавливается программно: auth()->id()
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Поле из $fillable модели Favorite
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            
            $table->timestamps();

            // Индексы
            $table->index('user_id');
            $table->index('product_id');
            
            // Уникальный индекс для предотвращения дубликатов
            $table->unique(['user_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};