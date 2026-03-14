<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emballages', function (Blueprint $table) {
            // packaging_batch_id was in fillable/form but missing from schema
            if (!Schema::hasColumn('emballages', 'packaging_batch_id')) {
                $table->string('packaging_batch_id')->nullable()->after('id');
            }

            // envelope_stock_id: for Box packaging — deduct 12 envelopes per box
            if (!Schema::hasColumn('emballages', 'envelope_stock_id')) {
                $table->foreignId('envelope_stock_id')
                    ->nullable()
                    ->after('raw_material_stock_id')
                    ->constrained('raw_material_stocks')
                    ->nullOnDelete();
            }
        });

        // sales.batches JSON column was missing — needed for multi-batch sales
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'batches')) {
                $table->json('batches')->nullable()->after('item');
            }
        });
    }

    public function down(): void
    {
        Schema::table('emballages', function (Blueprint $table) {
            if (Schema::hasColumn('emballages', 'envelope_stock_id')) {
                $table->dropForeign(['envelope_stock_id']);
                $table->dropColumn('envelope_stock_id');
            }
            if (Schema::hasColumn('emballages', 'packaging_batch_id')) {
                $table->dropColumn('packaging_batch_id');
            }
        });

        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'batches')) {
                $table->dropColumn('batches');
            }
        });
    }
};
