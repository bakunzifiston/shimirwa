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
        Schema::create('emballages', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('milling_id')
            ->constrained('millings')
            ->cascadeOnDelete();
            $table->string('item');
             $table->string('box')->nullable();
            $table->foreignId('raw_material_stock_id') // <-- link batch directly
        ->nullable()
        ->constrained('raw_material_stocks')
        ->nullOnDelete();// e.g. bags, stickers, cartons
            $table->string('packaging_type')->default(0); // quantity received
            $table->string('quantity')->default(0); // 
            $table->integer('damaged')->default(0);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 12, 2); // 
            $table->date('expiry_date')->nullable();
            $table->string('batch')->nullable();
            $table->string('comment')->nullable(0);
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade'); // Responsible staff
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emballages');
    }
};
