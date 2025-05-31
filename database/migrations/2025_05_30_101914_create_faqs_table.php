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
        Schema::dropIfExists('faqs');

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            
            // Поля из $fillable модели
            $table->string('question');
            $table->text('answer');
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            
            // Дополнительные поля, которых нет в $fillable модели
            // Уже отмечено как nullable, оставляем как есть
            $table->string('category')->nullable();
            $table->timestamps();
            
            // Индексы
            $table->index('is_active');
            $table->index('category');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
