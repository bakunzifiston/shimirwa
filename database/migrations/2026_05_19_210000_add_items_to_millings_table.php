<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
    }

    public function down(): void
    {
        if (Schema::hasColumn('millings', 'items')) {
            Schema::table('millings', function (Blueprint $table) {
                $table->dropColumn('items');
            });
        }
    }
};
