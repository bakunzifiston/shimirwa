<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_catalog')) {
            return;
        }

        Schema::table('product_catalog', function (Blueprint $table) {
            if (! Schema::hasColumn('product_catalog', 'requires_sorting')) {
                $table->boolean('requires_sorting')->default(false)->after('is_active');
            }
            if (! Schema::hasColumn('product_catalog', 'requires_roasting')) {
                $table->boolean('requires_roasting')->default(false)->after(
                    Schema::hasColumn('product_catalog', 'requires_sorting') ? 'requires_sorting' : 'is_active'
                );
            }
        });

        if (DB::table('product_catalog')->count() > 0) {
            return;
        }

        $now = now();
        DB::table('product_catalog')->insert([
            [
                'name' => 'Maize',
                'category' => 'production',
                'sub_category' => 'Raw Material',
                'unit' => 'kg',
                'description' => null,
                'is_active' => true,
                'requires_sorting' => true,
                'requires_roasting' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Soy',
                'category' => 'production',
                'sub_category' => 'Raw Material',
                'unit' => 'kg',
                'description' => null,
                'is_active' => true,
                'requires_sorting' => true,
                'requires_roasting' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Sorghum',
                'category' => 'production',
                'sub_category' => 'Raw Material',
                'unit' => 'kg',
                'description' => null,
                'is_active' => true,
                'requires_sorting' => true,
                'requires_roasting' => false,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Wheat',
                'category' => 'production',
                'sub_category' => 'Raw Material',
                'unit' => 'kg',
                'description' => null,
                'is_active' => true,
                'requires_sorting' => true,
                'requires_roasting' => false,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::table('product_catalog', function (Blueprint $table) {
            if (Schema::hasColumn('product_catalog', 'requires_sorting')) {
                $table->dropColumn('requires_sorting');
            }
            if (Schema::hasColumn('product_catalog', 'requires_roasting')) {
                $table->dropColumn('requires_roasting');
            }
        });
    }
};
