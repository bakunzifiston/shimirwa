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
        Schema::create('roastings', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->float('quantity_in');
            $table->float('loss')->default(0);
            $table->string('batch');
            
            // Foreign keys for Chef and Supervisor
            $table->foreignId('chef_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('supervisor_id')->constrained('employees')->onDelete('cascade');
        
            // Either from raw_material_stock or sorting
            $table->foreignId('raw_material_stock_id')->nullable()->constrained('raw_material_stocks')->onDelete('cascade');
            $table->foreignId('sorting_id')->nullable()->constrained('sortings')->onDelete('cascade');
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roastings');
    }
};
