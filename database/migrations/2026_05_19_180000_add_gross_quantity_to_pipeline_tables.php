<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sortings', function (Blueprint $table) {
            $table->decimal('gross_quantity', 12, 2)->nullable()->after('quantity_in');
        });

        Schema::table('roastings', function (Blueprint $table) {
            $table->decimal('gross_quantity', 12, 2)->nullable()->after('quantity_in');
        });

        // Best-effort backfill: gross = usable + loss (accurate when no downstream consumption yet).
        DB::table('sortings')->whereNull('gross_quantity')->update([
            'gross_quantity' => DB::raw('quantity_in + COALESCE(loss, 0)'),
        ]);

        DB::table('roastings')->whereNull('gross_quantity')->update([
            'gross_quantity' => DB::raw('quantity_in + COALESCE(loss, 0)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('sortings', function (Blueprint $table) {
            $table->dropColumn('gross_quantity');
        });

        Schema::table('roastings', function (Blueprint $table) {
            $table->dropColumn('gross_quantity');
        });
    }
};
