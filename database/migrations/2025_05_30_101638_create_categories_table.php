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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            
            // Поля из $fillable модели Category
            $table->string('name');
            $table->string('slug')->unique(); // Добавлено для модели
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            
            $table->timestamps();
            
            // Индексы
            $table->index('slug'); // Для быстрого поиска по slug
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};