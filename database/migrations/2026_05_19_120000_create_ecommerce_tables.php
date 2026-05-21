<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('discount_price', 12, 2)->nullable();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address');
            $table->timestamps();

            $table->index('phone');
        });

        Schema::create('shop_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('total', 12, 2);
            $table->string('payment_status', 20)->default('pending');
            $table->string('order_status', 20)->default('new');
            $table->string('idempotency_key', 64)->unique()->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['order_status', 'payment_status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_order_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity_change');
            $table->unsignedInteger('quantity_before');
            $table->unsignedInteger('quantity_after');
            $table->string('reason', 50);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('shop_orders');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
    }
};
