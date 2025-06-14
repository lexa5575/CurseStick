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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 40)->nullable(); // Длина Laravel session ID
            $table->timestamp('expires_at')->nullable(); // TTL для очистки старых корзин
            $table->timestamps();
            
            // Индексы для производительности
            $table->index('session_id');
            $table->index('expires_at'); // Для автоочистки
            $table->index(['user_id', 'session_id']); // Составной индекс
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};