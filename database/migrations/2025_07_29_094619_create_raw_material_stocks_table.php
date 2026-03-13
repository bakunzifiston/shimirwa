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
        Schema::create('raw_material_stocks', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('type'); // <-- added type column (e.g., Grain, Seed, Other)
            $table->string('item'); // Maize, Sorghum, etc.
            $table->float('received')->default(0);
            $table->float('rejected')->default(0); // Positive or negative
            $table->float('quantity_in')->default(0);
            $table->string('comment')->nullable(); // Additional notes or comments
            $table->string('batch_number');
            // Foreign key to employees table (Responsible Staff)
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raw_material_stocks');
    }
};
