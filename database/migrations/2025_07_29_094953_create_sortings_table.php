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
        Schema::create('sortings', function (Blueprint $table) {
            $table->id();
            $table->date('date');
        
            // Link to the exact raw material stock (batch + item)
            $table->foreignId('raw_material_stock_id')
                ->constrained('raw_material_stocks')
                ->onDelete('cascade');
        
            $table->float('quantity_in');
            $table->float('loss')->default(0); // Loss in kg
        
            // Foreign key to employee (Responsible Staff)
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->onDelete('cascade');
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sortings');
    }
};
