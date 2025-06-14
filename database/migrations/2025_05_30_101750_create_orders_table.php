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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // Unique order number for clients

            // System fields (NOT in $fillable - set programmatically)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('total', 10, 2)->default(0); // Calculated from order_items
            $table->string('payment_status')->default('pending'); // Set by controller
            $table->string('payment_invoice_id')->nullable(); // Set by payment system
            $table->string('payment_token', 64)->unique()->nullable(); // Secure token for payment URL
            $table->string('tracking_number')->nullable(); // Set by admin


            // Fields from Order model $fillable (safe for mass assignment)
            $table->string('status')->default('pending');
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('street');
            $table->string('house')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->string('country');
            $table->string('phone')->nullable();
            $table->string('email');
            $table->text('comment')->nullable();
            $table->string('payment_method')->nullable();

            $table->timestamps();

            // Indexes for fast search and filtering
            $table->index('user_id');
            $table->index('status');
            $table->index('payment_status');
            $table->index('payment_token'); // For fast token-based search
            $table->index('created_at');
            // Composite index for frequent queries
            $table->index(['user_id', 'status', 'created_at']);
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
