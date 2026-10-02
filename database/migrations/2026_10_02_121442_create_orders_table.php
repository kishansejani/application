<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('invoice_number')->unique()->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            
            // Address snapshot
            $table->foreignId('address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->text('delivery_address');
            $table->string('delivery_city')->nullable();
            $table->string('delivery_pincode')->nullable();
            $table->decimal('delivery_lat', 10, 8)->nullable();
            $table->decimal('delivery_lng', 11, 8)->nullable();
            
            // Delivery Timing Logic
            $table->string('delivery_type')->default('two_hours'); // 'two_hours', 'next_day'
            $table->string('delivery_slot'); // e.g. "Today within 2 Hours (By 02:30 PM)" or "Tomorrow Delivery (09:00 AM - 12:00 PM)"
            $table->timestamp('estimated_delivery_at')->nullable();
            
            // Financials
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('coupon_code')->nullable();
            $table->decimal('delivery_charge', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            
            // Payment
            $table->string('payment_method')->default('cod'); // 'cod', 'upi', 'card', 'netbanking'
            $table->string('payment_status')->default('pending'); // 'pending', 'paid', 'failed', 'refunded'
            $table->string('transaction_id')->nullable();
            
            // Order Status Workflow
            $table->string('order_status')->default('pending'); // 'pending', 'confirmed', 'processing', 'out_for_delivery', 'delivered', 'cancelled'
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
