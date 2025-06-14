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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();

            // Поля из $fillable модели Banner
            $table->string('image');
            $table->text('text')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->string('url')->nullable();
            $table->string('button_text')->nullable();
            $table->string('text_color')->default('#FFFFFF')->nullable();
            $table->string('text_size')->default('text-4xl')->nullable();
            $table->string('text_weight')->default('font-bold')->nullable();
            $table->string('text_shadow')->default('shadow-none')->nullable();
            $table->string('text_alignment')->default('text-center')->nullable();
            $table->string('overlay_color')->default('bg-black/40')->nullable();
            $table->text('subtitle')->nullable();
            
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