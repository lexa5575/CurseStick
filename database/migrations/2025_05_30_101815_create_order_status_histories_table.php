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
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('status');
            $table->text('comment')->nullable(); // Для заметок админа
            $table->foreignId('created_by')->nullable()->constrained('users'); // Кто изменил статус
            $table->timestamps();
            
            // Индексы
            $table->index('order_id');
            $table->index('created_at'); // Для сортировки истории
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};