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
        if (! Schema::hasTable('packaging_catalogs')) {
            Schema::create('packaging_catalogs', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('kg_per_unit', 8, 3);
                $table->boolean('manual_weight')->default(false);
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('emballages', function (Blueprint $table) {
            if (! Schema::hasColumn('emballages', 'packaging_catalog_id')) {
                $table->foreignId('packaging_catalog_id')
                    ->nullable()
                    ->after('milling_id')
                    ->constrained('packaging_catalogs')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('emballages', 'packaging_batch_id')) {
                $table->string('packaging_batch_id')->nullable()->after('date');
            }

            if (! Schema::hasColumn('emballages', 'envelope_stock_id')) {
                $table->foreignId('envelope_stock_id')
                    ->nullable()
                    ->after('raw_material_stock_id')
                    ->constrained('raw_material_stocks')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('emballages', function (Blueprint $table) {
            if (Schema::hasColumn('emballages', 'packaging_catalog_id')) {
                $table->dropConstrainedForeignId('packaging_catalog_id');
            }
        });
    }
};
