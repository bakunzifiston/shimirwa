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
        Schema::table('packaging_catalogs', function (Blueprint $table) {
            if (! Schema::hasColumn('packaging_catalogs', 'inner_unit_catalog_id')) {
                $table->foreignId('inner_unit_catalog_id')
                    ->nullable()->after('description')
                    ->constrained('packaging_catalogs')->nullOnDelete();
            }
            if (! Schema::hasColumn('packaging_catalogs', 'inner_units_per_package')) {
                $table->unsignedSmallInteger('inner_units_per_package')->default(0)->after(
                    Schema::hasColumn('packaging_catalogs', 'inner_unit_catalog_id') ? 'inner_unit_catalog_id' : 'description'
                );
            }
        });
    }

    public function down(): void
    {
        Schema::table('packaging_catalogs', function (Blueprint $table) {
            $table->dropForeign(['inner_unit_catalog_id']);
            $table->dropColumn(['inner_unit_catalog_id', 'inner_units_per_package']);
        });
    }
};
