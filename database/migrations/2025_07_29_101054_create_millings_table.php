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
        Schema::create('millings', function (Blueprint $table) {
            $table->id();
            $table->date('date');
        
            // Foreign keys to specific stock batches
            $table->foreignId('soy_stock_id')->nullable()
                ->constrained('roastings')->nullOnDelete();
        
            $table->foreignId('sorghum_stock_id')->nullable()
                ->constrained('raw_material_stocks')->nullOnDelete();
        
            $table->foreignId('wheat_stock_id')->nullable()
                ->constrained('raw_material_stocks')->nullOnDelete();
        
            $table->foreignId('maize_stock_id')->nullable()
                ->constrained('roastings')->nullOnDelete();
        
            // Quantities
            $table->float('soy')->default(0);
            $table->float('sorghum')->default(0);
            $table->float('wheat')->default(0);
            $table->float('maize')->default(0);
        
            $table->float('total_mixed_quantity');
            $table->float('output_flour');
            $table->float('loss')->default(0);
            $table->string('batch_number');
        
            // Employee
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('millings');
    }
};
