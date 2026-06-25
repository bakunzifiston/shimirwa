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
        Schema::table('emballages', function (Blueprint $table) {
            // Add FK to packaging catalog (nullable so existing rows don't break)
            $table->foreignId('packaging_catalog_id')
                ->nullable()
                ->after('milling_id')
                ->constrained('packaging_catalogs')
                ->nullOnDelete();

            // Also add packaging_batch_id and envelope_stock_id if they don't exist
            // (original migration may have been created without them)
            if (!\Schema::hasColumn('emballages', 'packaging_batch_id')) {
                $table->string('packaging_batch_id')->nullable()->after('date');
            }
            if (!\Schema::hasColumn('emballages', 'envelope_stock_id')) {
                $table->foreignId('envelope_stock_id')
                    ->nullable()
                    ->after('raw_material_stock_id')
                    ->constrained('raw_material_stocks')
                    ->nullOnDelete();
            }
        });

        // Keep packaging_type for backward compat — we'll phase it out gradually
        // It stays in the table as a fallback display label
    }

    public function down(): void
    {
        Schema::table('emballages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('packaging_catalog_id');
        });
    }
};
