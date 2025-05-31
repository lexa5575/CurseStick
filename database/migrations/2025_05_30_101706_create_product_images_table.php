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
        // Очищаем таблицу, если существует
        Schema::dropIfExists('product_images');
        
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            // Изменено название поля с image_path на image для соответствия модели
            $table->string('image');
            // Удалены поля alt_text и is_main, которых нет в модели
            $table->integer('order')->default(0);
            $table->timestamps();
            
            // Индексы
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
