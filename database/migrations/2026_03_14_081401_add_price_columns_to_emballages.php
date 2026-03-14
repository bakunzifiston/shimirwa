<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emballages', function (Blueprint $table) {
            if (!Schema::hasColumn('emballages', 'unit_price')) {
                $table->decimal('unit_price', 10, 2)->nullable()->after('damaged');
            }
            if (!Schema::hasColumn('emballages', 'total_price')) {
                $table->decimal('total_price', 12, 2)->nullable()->after('unit_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('emballages', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'total_price']);
        });
    }
};
