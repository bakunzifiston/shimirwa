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
        if (Schema::hasTable('clients')) {
            return;
        }

        Schema::create('clients', function (Blueprint $table) {
                $table->id();
                $table->string('full_name');
                $table->enum('role', ['client', 'supplier'])->default('client'); // Distinguish type
                $table->string('client_type')->nullable(); // e.g., Individual, Company, Reseller
                $table->string('supplier_code')->nullable()->unique(); // Only for suppliers
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('address')->nullable();
                $table->timestamps();
            });
            

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
