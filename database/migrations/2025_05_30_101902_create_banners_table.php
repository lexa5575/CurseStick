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
        Schema::dropIfExists('banners');

        Schema::create('banners', function (Blueprint $table) {
            $table->id();

            // Поля из $fillable модели
            $table->string('image');
            $table->text('text')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            
            // Дополнительные поля для URL и кнопки
            $table->string('url')->nullable();
            $table->string('button_text')->nullable();
            
            // Новые поля для стилизации текста
            $table->string('text_color')->default('#FFFFFF')->nullable(); // Цвет текста
            $table->string('text_size')->default('text-4xl')->nullable(); // Размер шрифта (tailwind классы)
            $table->string('text_weight')->default('font-bold')->nullable(); // Толщина шрифта
            $table->string('text_shadow')->default('shadow-none')->nullable(); // Тень текста
            $table->string('text_alignment')->default('text-center')->nullable(); // Выравнивание текста
            $table->string('overlay_color')->default('bg-black/40')->nullable(); // Цвет наложения 
            $table->text('subtitle')->nullable(); // Подзаголовок баннера
            $table->timestamps();
            
            // Индексы
            $table->index('is_active');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
