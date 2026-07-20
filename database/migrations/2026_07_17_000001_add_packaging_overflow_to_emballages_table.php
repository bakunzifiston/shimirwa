<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emballages', function (Blueprint $table) {
            // [{stock_id, units}, ...] extra packaging-material draws when the
            // primary batch doesn't have enough units
            $table->json('packaging_overflow')->nullable()->after('milling_overflow');
        });
    }

    public function down(): void
    {
        Schema::table('emballages', function (Blueprint $table) {
            $table->dropColumn('packaging_overflow');
        });
    }
};
