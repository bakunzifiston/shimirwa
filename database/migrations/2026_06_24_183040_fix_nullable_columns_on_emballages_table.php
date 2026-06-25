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
            $table->string('comment')->nullable()->change();
            $table->decimal('unit_price', 10, 2)->nullable()->change();
            $table->decimal('total_price', 12, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('emballages', function (Blueprint $table) {
            $table->string('comment')->nullable(false)->default('')->change();
            $table->decimal('unit_price', 10, 2)->nullable(false)->change();
            $table->decimal('total_price', 12, 2)->nullable(false)->change();
        });
    }
};
