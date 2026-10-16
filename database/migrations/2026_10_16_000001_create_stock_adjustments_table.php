<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stock corrections from physical counts (see `php artisan stock:apply-count`).
 * Each row moves one batch's balance by quantity_change on `date`, so the
 * "as of" balances in the monthly report agree with what was counted.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock_adjustments')) {
            return;
        }

        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('stock_type', 20);          // raw | sorting | roasting | milling | packaged
            $table->unsignedBigInteger('stock_id');    // id in the matching table
            $table->decimal('book_quantity', 14, 3);   // system balance as of `date`, before the adjustment
            $table->decimal('counted_quantity', 14, 3);
            $table->decimal('quantity_change', 14, 3); // counted - book
            $table->string('reference')->nullable();   // batch number as written on the count sheet
            $table->string('reason', 40)->default('closing_count');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['stock_type', 'stock_id']);
            $table->index(['date', 'reason']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
