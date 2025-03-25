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
        // 10 digit limit, 2 decimal places
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('total_balance', 10, 2)->default(00.00)->change();
        });

        Schema::table('group_users', function (Blueprint $table) {
            $table->decimal('balance', 10, 2)->change();
        });

        Schema::table('debts', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
        });

        Schema::table('shares', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('decimal', function (Blueprint $table) {
            //
        });
    }
};
