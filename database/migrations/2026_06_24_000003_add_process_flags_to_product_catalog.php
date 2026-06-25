<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_catalog', function (Blueprint $table) {
            $table->boolean('requires_sorting')->default(false)->after('is_active');
            $table->boolean('requires_roasting')->default(false)->after('requires_sorting');
        });
    }

    public function down(): void
    {
        Schema::table('product_catalog', function (Blueprint $table) {
            $table->dropColumn(['requires_sorting', 'requires_roasting']);
        });
    }
};
