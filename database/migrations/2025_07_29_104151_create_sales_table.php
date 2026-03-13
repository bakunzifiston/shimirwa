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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->date('date');
        
            // Product sold
            $table->string('item'); // e.g. flour, maize flour, soy flour
            $table->integer('quantity'); // sold quantity (kg or units)
               $table->integer('returned')
                  ->default(0)
                  ->after('quantity'); // how many items were returned

            $table->string('reason')
                  ->nullable()
                  ->after('returned'); // reason for return
        
            // Pricing
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('total_price', 12, 2);
        
            // Traceability
            $table->string('batch')->nullable(); // batch of product sold
        
            // Relationships
            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->onDelete('cascade'); // buyer
        
            $table->foreignId('employee_id')
                  ->constrained('employees')
                  ->onDelete('cascade'); // staff responsible
        
            $table->foreignId('emballage_id')
                  ->nullable()
                  ->constrained('emballages')
                  ->nullOnDelete(); // packaging used
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
