<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['sortings', 'roastings'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->decimal('quantity_remaining', 12, 2)->nullable()->after('loss');
            });
        }

        // Existing rows stored usable output in quantity_in; gross was in gross_quantity when present.
        foreach (['sortings', 'roastings'] as $table) {
            if (Schema::hasColumn($table, 'gross_quantity')) {
                DB::table($table)->update([
                    'quantity_remaining' => DB::raw('quantity_in'),
                    'quantity_in' => DB::raw('COALESCE(gross_quantity, quantity_in + COALESCE(loss, 0))'),
                ]);
            } else {
                DB::table($table)->update([
                    'quantity_remaining' => DB::raw('quantity_in'),
                    'quantity_in' => DB::raw('quantity_in + COALESCE(loss, 0)'),
                ]);
            }
        }

        foreach (['sortings', 'roastings'] as $table) {
            if (Schema::hasColumn($table, 'gross_quantity')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropColumn('gross_quantity');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['sortings', 'roastings'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->decimal('gross_quantity', 12, 2)->nullable()->after('quantity_in');
            });

            DB::table($table)->update([
                'gross_quantity' => DB::raw('quantity_in'),
                'quantity_in' => DB::raw('quantity_remaining'),
            ]);

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('quantity_remaining');
            });
        }
    }
};
