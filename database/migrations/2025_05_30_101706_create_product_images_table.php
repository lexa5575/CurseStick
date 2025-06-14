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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            
            // Поля из $fillable модели ProductImage
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('image');
            $table->integer('order')->default(0);
            
            $table->timestamps();
            
            // Индексы
            $table->index('product_id');
            $table->index('order'); // Для сортировки изображений
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