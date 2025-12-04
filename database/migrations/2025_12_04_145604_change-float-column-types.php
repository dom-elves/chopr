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
        Schema::table('group_users', function (Blueprint $table) {
            $table->bigInteger('balance')->default(0)->change();
        });

        Schema::table('debts', function (Blueprint $table) {
            $table->bigInteger('amount')->default(0)->change();
        });

        Schema::table('shares', function (Blueprint $table) {
            $table->bigInteger('amount')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
