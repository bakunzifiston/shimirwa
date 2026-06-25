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
            // FK to the inner packaging type (e.g. Box → 1kg bag catalog entry)
            $table->foreignId('inner_unit_catalog_id')
                  ->nullable()->after('description')
                  ->constrained('packaging_catalogs')->nullOnDelete();
            // How many inner units are inside one outer package (e.g. 12 bags per box)
            $table->unsignedSmallInteger('inner_units_per_package')->default(0)->after('inner_unit_catalog_id');
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
