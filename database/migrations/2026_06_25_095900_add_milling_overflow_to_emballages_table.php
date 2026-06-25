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
        Schema::table('emballages', function (Blueprint $table) {
            if (! Schema::hasColumn('emballages', 'milling_overflow')) {
                $table->json('milling_overflow')->nullable()->after('milling_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('emballages', function (Blueprint $table) {
            $table->dropColumn('milling_overflow');
        });
    }
};
