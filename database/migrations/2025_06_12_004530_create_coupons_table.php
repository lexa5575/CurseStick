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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Coupon code (uppercase)
            $table->string('name'); // Display name for admin
            $table->text('description')->nullable(); // Description for admin
            
            // Discount settings
            $table->enum('discount_type', ['fixed', 'percentage']); // $ or %
            $table->decimal('discount_value', 10, 2); // Amount or percentage value
            
            // Status
            $table->boolean('is_active')->default(true);
            
            // Validity period
            $table->timestamp('valid_from')->nullable(); // Start date (optional)
            $table->timestamp('valid_until')->nullable(); // End date (null = permanent)
            
            // Usage limits
            $table->integer('usage_limit')->nullable(); // Max uses (null = unlimited)
            $table->integer('usage_count')->default(0); // Current usage count
            
            // Category restrictions
            $table->boolean('applies_to_all_categories')->default(true); // All categories or specific ones
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('code');
            $table->index('is_active');
            $table->index(['valid_from', 'valid_until']);
            $table->index('applies_to_all_categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};