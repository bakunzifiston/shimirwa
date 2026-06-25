<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // 'ecommerce' = public shop product | 'production' = factory input (raw material / packaging)
            $table->string('category', 30)->default('production');
            // Sub-type within category, e.g. 'Raw Material', 'Packaging', 'Finished Good'
            $table->string('sub_category', 60)->nullable();
            $table->string('unit', 30)->default('kg'); // kg, units, pcs …
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_catalog');
    }
};
