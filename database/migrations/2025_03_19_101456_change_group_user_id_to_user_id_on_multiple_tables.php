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
        Schema::table('groups', function (Blueprint $table) {
            $table->renameColumn('owner_id', 'user_id');
        });

        Schema::table('debts', function (Blueprint $table) {
            $table->renameColumn('collector_group_user_id', 'user_id');
        });

        Schema::table('shares', function (Blueprint $table) {
            $table->renameColumn('group_user_id', 'user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->renameColumn('user_id', 'owner_id' );
        });

        Schema::table('debts', function (Blueprint $table) {
            $table->renameColumn('user_id', 'collector_group_user_id');
        });

        Schema::table('shares', function (Blueprint $table) {
            $table->renameColumn('user_id', 'group_user_id');
        });
    }
};
