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
        Schema::table('millings', function (Blueprint $table) {
            $table->json('items')->nullable()->after('date');

            // Drop old hardcoded foreign keys first, then their columns
            $table->dropForeign(['soy_stock_id']);
            $table->dropForeign(['maize_stock_id']);
            $table->dropForeign(['sorghum_stock_id']);
            $table->dropForeign(['wheat_stock_id']);

            $table->dropColumn([
                'soy_stock_id', 'maize_stock_id', 'sorghum_stock_id', 'wheat_stock_id',
                'soy', 'maize', 'sorghum', 'wheat',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('millings', function (Blueprint $table) {
            $table->dropColumn('items');

            $table->foreignId('soy_stock_id')->nullable()->constrained('roastings')->nullOnDelete();
            $table->foreignId('sorghum_stock_id')->nullable()->constrained('raw_material_stocks')->nullOnDelete();
            $table->foreignId('wheat_stock_id')->nullable()->constrained('raw_material_stocks')->nullOnDelete();
            $table->foreignId('maize_stock_id')->nullable()->constrained('roastings')->nullOnDelete();
            $table->float('soy')->default(0);
            $table->float('sorghum')->default(0);
            $table->float('wheat')->default(0);
            $table->float('maize')->default(0);
        });
    }
};
