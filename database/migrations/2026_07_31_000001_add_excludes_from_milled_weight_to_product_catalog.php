<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_catalog', function (Blueprint $table) {
            // Only meaningful for direct_to_milling items (e.g. sugar): when true, the
            // item is still deducted from stock as a milling ingredient, but its kg is
            // excluded from total_mixed_quantity / output_flour (it was never milled).
            $table->boolean('excludes_from_milled_weight')->default(false)->after('direct_to_milling');
        });
    }

    public function down(): void
    {
        Schema::table('product_catalog', function (Blueprint $table) {
            $table->dropColumn('excludes_from_milled_weight');
        });
    }
};
