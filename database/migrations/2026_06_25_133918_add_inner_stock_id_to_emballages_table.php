<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emballages', function (Blueprint $table) {
            if (! Schema::hasColumn('emballages', 'inner_stock_id')) {
                $table->foreignId('inner_stock_id')
                    ->nullable()->after('raw_material_stock_id')
                    ->constrained('raw_material_stocks')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('emballages', function (Blueprint $table) {
            $table->dropForeign(['inner_stock_id']);
            $table->dropColumn('inner_stock_id');
        });
    }
};
