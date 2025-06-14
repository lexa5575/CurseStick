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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            
            // Поля из $fillable модели Product (в правильном порядке)
            $table->string('name');
            $table->string('slug')->unique(); // Добавлено для модели
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->decimal('discount', 10, 2)->default(0);
            $table->string('image')->nullable();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes(); // Из модели - use SoftDeletes
            
            // Индексы для быстрого поиска и фильтрации
            $table->index('slug');
            $table->index('category_id');
            $table->index('is_active');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};