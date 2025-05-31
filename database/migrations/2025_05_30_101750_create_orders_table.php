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
        Schema::dropIfExists('orders');
        
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending');
            $table->decimal('total', 10, 2);
            
            // Контактная информация
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            
            // Адрес доставки
            $table->string('street'); // Соответствует полю 'address' в форме
            $table->string('house')->nullable(); // Делаем house nullable, соответствует addressUnit
            $table->string('city');
            $table->string('state'); // Делаем state обязательным
            $table->string('postal_code'); // Соответствует zipcode в форме
            $table->string('country');
            
            // Дополнительная информация
            $table->text('comment')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('pending');
            // tracking_number убран по запросу
            
            $table->timestamps();
            
            // Индексы для быстрого поиска и фильтрации
            $table->index('user_id');
            $table->index('status');
            $table->index('payment_status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
