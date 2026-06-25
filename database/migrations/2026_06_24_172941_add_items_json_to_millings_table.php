<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('millings', 'items')) {
            Schema::table('millings', function (Blueprint $table) {
                $table->json('items')->nullable()->after('date');
            });
        }

        $database = Schema::getConnection()->getDatabaseName();

        foreach (['soy_stock_id', 'maize_stock_id', 'sorghum_stock_id', 'wheat_stock_id'] as $column) {
            if (! Schema::hasColumn('millings', $column)) {
                continue;
            }

            $foreignKey = DB::selectOne(
                'SELECT CONSTRAINT_NAME AS name
                 FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = ?
                   AND TABLE_NAME = ?
                   AND COLUMN_NAME = ?
                   AND REFERENCED_TABLE_NAME IS NOT NULL
                 LIMIT 1',
                [$database, 'millings', $column]
            );

            if ($foreignKey?->name) {
                Schema::table('millings', function (Blueprint $table) use ($foreignKey) {
                    $table->dropForeign($foreignKey->name);
                });
            }
        }

        $legacyColumns = array_values(array_filter(
            ['soy_stock_id', 'maize_stock_id', 'sorghum_stock_id', 'wheat_stock_id', 'soy', 'maize', 'sorghum', 'wheat'],
            fn (string $column) => Schema::hasColumn('millings', $column)
        ));

        if ($legacyColumns !== []) {
            Schema::table('millings', function (Blueprint $table) use ($legacyColumns) {
                $table->dropColumn($legacyColumns);
            });
        }
    }

    public function down(): void
    {
        Schema::table('millings', function (Blueprint $table) {
            if (Schema::hasColumn('millings', 'items')) {
                $table->dropColumn('items');
            }

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
