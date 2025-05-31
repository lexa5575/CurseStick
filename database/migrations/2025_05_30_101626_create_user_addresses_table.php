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
        // Сначала удаляем таблицу, если она существует
        Schema::dropIfExists('user_addresses');
        
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('street');
            $table->string('house');
            $table->string('city');
            $table->string('postal_code');
            $table->string('country');
            $table->boolean('is_main')->default(false);
            $table->timestamps();
            
            // Индекс для быстрого доступа к адресам конкретного пользователя
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
