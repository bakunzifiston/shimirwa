<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_catalog', function (Blueprint $table) {
            $table->boolean('direct_to_milling')->default(false)->after('requires_roasting');
        });
    }

    public function down(): void
    {
        Schema::table('product_catalog', function (Blueprint $table) {
            $table->dropColumn('direct_to_milling');
        });
    }
};
